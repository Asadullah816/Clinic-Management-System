<?php

namespace App\Http\Controllers;

use App\Models\Implant;
use App\Models\ImplantBuyer;
use App\Models\ImplantPayment;
use App\Models\ImplantSale;
use App\Models\ImplantSaleItem;
use App\Models\ImplantStockTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ImplantSaleController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $buyerId = $request->query('buyer_id');
        $search = $request->query('search');

        $query = ImplantSale::with(['buyer', 'items.implant']);

        if ($status) {
            $query->where('payment_status', $status);
        }

        if ($buyerId) {
            $query->where('implant_buyer_id', $buyerId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('sale_number', 'like', "%{$search}%")
                    ->orWhere('patient_reference', 'like', "%{$search}%")
                    ->orWhereHas('buyer', function ($b) use ($search) {
                        $b->where('doctor_name', 'like', "%{$search}%")
                            ->orWhere('clinic_hospital_name', 'like', "%{$search}%");
                    });
            });
        }

        $sales = $query->latest('sale_date')->latest('id')->paginate(15)->withQueryString();

        $stats = [
            'total_sales' => ImplantSale::count(),
            'total_amount' => (float) ImplantSale::sum('total_amount'),
            'total_paid' => (float) ImplantSale::sum('paid_amount'),
            'total_due' => (float) ImplantSale::sum('due_amount'),
        ];

        $buyers = ImplantBuyer::where('status', 'active')->orderBy('doctor_name')->get();

        return view('implant-sales.index', compact('sales', 'stats', 'buyers', 'status', 'buyerId', 'search'));
    }

    public function outstanding(Request $request)
    {
        $buyerId = $request->query('buyer_id');
        $search = $request->query('search');

        $query = ImplantSale::with(['buyer', 'items.implant'])
            ->where('due_amount', '>', 0);

        if ($buyerId) {
            $query->where('implant_buyer_id', $buyerId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('sale_number', 'like', "%{$search}%")
                    ->orWhereHas('buyer', function ($b) use ($search) {
                        $b->where('doctor_name', 'like', "%{$search}%")
                            ->orWhere('clinic_hospital_name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        $sales = $query->latest('sale_date')->paginate(15)->withQueryString();

        $totalOutstanding = (float) ImplantSale::where('due_amount', '>', 0)->sum('due_amount');
        $buyers = ImplantBuyer::where('status', 'active')->orderBy('doctor_name')->get();

        return view('implant-sales.outstanding', compact('sales', 'totalOutstanding', 'buyers', 'buyerId', 'search'));
    }

    public function create(Request $request)
    {
        $preselectedBuyerId = $request->query('buyer_id');

        $buyers = ImplantBuyer::where('status', 'active')
            ->orderBy('is_internal', 'desc')
            ->orderBy('doctor_name')
            ->get();

        $implants = Implant::where('status', 'active')
            ->orderBy('brand')
            ->orderBy('name')
            ->get();

        return view('implant-sales.create', compact('buyers', 'implants', 'preselectedBuyerId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'implant_buyer_id' => 'required|exists:implant_buyers,id',
            'sale_date' => 'required|date',
            'patient_reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'discount' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string|max:50',
            'items' => 'required|array|min:1',
            'items.*.implant_id' => 'required|exists:implants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        // Verify stock availability for each item
        $implantsById = Implant::whereIn('id', collect($validated['items'])->pluck('implant_id'))->get()->keyBy('id');
        $stockErrors = [];

        foreach ($validated['items'] as $index => $itemData) {
            $implant = $implantsById->get($itemData['implant_id']);
            if (! $implant) {
                continue;
            }

            if ($itemData['quantity'] > $implant->stock_quantity) {
                $stockErrors["items.{$index}.quantity"] = "Requested {$itemData['quantity']} units of {$implant->name}, but only {$implant->stock_quantity} units are in stock.";
            }
        }

        if (! empty($stockErrors)) {
            return back()->withErrors($stockErrors)->withInput();
        }

        $sale = DB::transaction(function () use ($validated, $implantsById) {
            $nextId = (ImplantSale::max('id') ?? 0) + 1;
            $saleNumber = 'IMP-SALE-'.str_pad($nextId, 4, '0', STR_PAD_LEFT);

            // Calculate subtotal
            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                $subtotal += ($item['quantity'] * $item['unit_price']);
            }

            $discount = (float) ($validated['discount'] ?? 0);
            $totalAmount = max(0, $subtotal - $discount);
            $paidAmount = min($totalAmount, (float) ($validated['paid_amount'] ?? 0));
            $dueAmount = max(0, $totalAmount - $paidAmount);

            $paymentStatus = $dueAmount <= 0
                ? ImplantSale::STATUS_PAID
                : ($paidAmount > 0 ? ImplantSale::STATUS_PARTIAL : ImplantSale::STATUS_PENDING);

            $sale = ImplantSale::create([
                'sale_number' => $saleNumber,
                'implant_buyer_id' => $validated['implant_buyer_id'],
                'sale_date' => $validated['sale_date'],
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'due_amount' => $dueAmount,
                'payment_status' => $paymentStatus,
                'patient_reference' => $validated['patient_reference'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            // Save items and decrement stock
            foreach ($validated['items'] as $itemData) {
                $implant = $implantsById->get($itemData['implant_id']);
                $totalItemPrice = $itemData['quantity'] * $itemData['unit_price'];

                ImplantSaleItem::create([
                    'implant_sale_id' => $sale->id,
                    'implant_id' => $implant->id,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'total_price' => $totalItemPrice,
                ]);

                // Reduce stock
                $newStock = $implant->stock_quantity - $itemData['quantity'];
                $implant->update(['stock_quantity' => $newStock]);

                // Log stock transaction
                ImplantStockTransaction::create([
                    'implant_id' => $implant->id,
                    'type' => 'sale',
                    'quantity' => -$itemData['quantity'],
                    'balance_after' => $newStock,
                    'reference_id' => $sale->id,
                    'notes' => "Sold via {$saleNumber}",
                    'created_by' => auth()->id(),
                ]);
            }

            // Create initial payment record if paid_amount > 0
            if ($paidAmount > 0) {
                $payNextId = (ImplantPayment::max('id') ?? 0) + 1;
                $paymentNumber = 'IMP-PAY-'.str_pad($payNextId, 4, '0', STR_PAD_LEFT);

                ImplantPayment::create([
                    'payment_number' => $paymentNumber,
                    'implant_sale_id' => $sale->id,
                    'implant_buyer_id' => $sale->implant_buyer_id,
                    'payment_date' => $sale->sale_date,
                    'amount' => $paidAmount,
                    'payment_method' => $validated['payment_method'] ?? 'cash',
                    'notes' => 'Settled at the time of sale',
                    'created_by' => auth()->id(),
                ]);
            }

            return $sale;
        });

        return redirect()->route('implant-sales.show', $sale)->with('success', 'Dental Implant sale recorded successfully.');
    }

    public function show(ImplantSale $implantSale)
    {
        $implantSale->load(['buyer', 'items.implant', 'payments.createdBy', 'createdBy', 'failedImplants.implant']);

        return view('implant-sales.show', compact('implantSale'));
    }

    public function print(ImplantSale $implantSale)
    {
        $implantSale->load(['buyer', 'items.implant', 'payments.createdBy', 'createdBy']);

        return view('implant-sales.print', compact('implantSale'));
    }

    public function destroy(ImplantSale $implantSale)
    {
        DB::transaction(function () use ($implantSale) {
            // Restore implant stock
            foreach ($implantSale->items as $item) {
                if ($item->implant) {
                    $newStock = $item->implant->stock_quantity + $item->quantity;
                    $item->implant->update(['stock_quantity' => $newStock]);

                    ImplantStockTransaction::create([
                        'implant_id' => $item->implant->id,
                        'type' => 'return',
                        'quantity' => $item->quantity,
                        'balance_after' => $newStock,
                        'reference_id' => $implantSale->id,
                        'notes' => "Reversed due to deletion of {$implantSale->sale_number}",
                        'created_by' => auth()->id(),
                    ]);
                }
            }

            $implantSale->delete();
        });

        return redirect()->route('implant-sales.index')->with('success', 'Implant sale record deleted and inventory restored successfully.');
    }
}
