<?php

namespace App\Http\Controllers;

use App\Models\FailedImplant;
use App\Models\Implant;
use App\Models\ImplantBuyer;
use App\Models\ImplantSale;
use App\Models\ImplantStockTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FailedImplantController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $source = $request->query('source');
        $reason = $request->query('reason');
        $stage = $request->query('stage');
        $warrantyStatus = $request->query('warranty_status');
        $buyerId = $request->query('buyer_id');
        $implantId = $request->query('implant_id');

        $query = FailedImplant::with(['implant', 'buyer', 'sale', 'replacementImplant', 'createdBy']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('failure_number', 'like', "%{$search}%")
                    ->orWhere('patient_name', 'like', "%{$search}%")
                    ->orWhere('tooth_number', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHas('buyer', function ($b) use ($search) {
                        $b->where('doctor_name', 'like', "%{$search}%")
                            ->orWhere('clinic_hospital_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('implant', function ($imp) use ($search) {
                        $imp->where('name', 'like', "%{$search}%")
                            ->orWhere('brand', 'like', "%{$search}%")
                            ->orWhere('item_code', 'like', "%{$search}%");
                    });
            });
        }

        if ($source) {
            $query->where('source', $source);
        }

        if ($reason) {
            $query->where('failure_reason', $reason);
        }

        if ($stage) {
            $query->where('failure_stage', $stage);
        }

        if ($warrantyStatus) {
            $query->where('warranty_status', $warrantyStatus);
        }

        if ($buyerId) {
            $query->where('implant_buyer_id', $buyerId);
        }

        if ($implantId) {
            $query->where('implant_id', $implantId);
        }

        $failedImplants = $query->latest('failure_date')->latest('id')->paginate(15)->withQueryString();

        // SEPARATE CALCULATIONS (Isolated from normal sales revenue and active inventory)
        $stats = [
            'total_failed_units' => (int) FailedImplant::sum('quantity'),
            'total_failed_cost' => (float) FailedImplant::sum('total_cost'),
            'vendor_replaced_units' => (int) FailedImplant::where('warranty_status', FailedImplant::WARRANTY_REPLACED)->sum('quantity'),
            'vendor_replaced_cost' => (float) FailedImplant::where('warranty_status', FailedImplant::WARRANTY_REPLACED)->sum('total_cost'),
            'clinic_loss_cost' => (float) FailedImplant::where('warranty_status', FailedImplant::WARRANTY_CLINIC_LOSS)->sum('total_cost'),
            'pending_warranty_count' => (int) FailedImplant::where('warranty_status', FailedImplant::WARRANTY_PENDING)->count(),
        ];

        $implants = Implant::orderBy('name')->get();
        $buyers = ImplantBuyer::orderBy('doctor_name')->get();

        return view('failed-implants.index', compact(
            'failedImplants',
            'stats',
            'implants',
            'buyers',
            'search',
            'source',
            'reason',
            'stage',
            'warrantyStatus',
            'buyerId',
            'implantId'
        ));
    }

    public function create(Request $request)
    {
        $implantId = $request->query('implant_id');
        $buyerId = $request->query('buyer_id');
        $saleId = $request->query('sale_id');
        $source = $request->query('source', $saleId ? 'sale' : 'stock');

        $implants = Implant::where('status', 'active')->orderBy('name')->get();
        $buyers = ImplantBuyer::where('status', 'active')->orderBy('doctor_name')->get();
        $sales = $buyerId ? ImplantSale::where('implant_buyer_id', $buyerId)->latest()->get() : ImplantSale::latest()->take(30)->get();

        $selectedImplant = $implantId ? Implant::find($implantId) : null;
        $selectedSale = $saleId ? ImplantSale::with('items.implant')->find($saleId) : null;
        $selectedBuyer = $buyerId ? ImplantBuyer::find($buyerId) : ($selectedSale?->buyer);

        return view('failed-implants.create', compact(
            'implants',
            'buyers',
            'sales',
            'selectedImplant',
            'selectedSale',
            'selectedBuyer',
            'source',
            'implantId',
            'buyerId',
            'saleId'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'source' => 'required|in:stock,sale,internal',
            'implant_id' => 'required|exists:implants,id',
            'implant_buyer_id' => 'nullable|exists:implant_buyers,id',
            'implant_sale_id' => 'nullable|exists:implant_sales,id',
            'failure_date' => 'required|date',
            'quantity' => 'required|integer|min:1',
            'unit_cost' => 'required|numeric|min:0',
            'failure_stage' => 'required|string',
            'failure_reason' => 'required|string',
            'patient_name' => 'nullable|string|max:255',
            'tooth_number' => 'nullable|string|max:50',
            'warranty_status' => 'required|string',
            'replacement_implant_id' => 'nullable|exists:implants,id',
            'notes' => 'nullable|string',
        ]);

        $failedImplant = DB::transaction(function () use ($validated) {
            // Generate sequence number
            $lastRecord = FailedImplant::latest('id')->first();
            $nextNumber = $lastRecord ? ($lastRecord->id + 1) : 1;
            $failureNumber = 'IMP-FAIL-'.date('Y').'-'.str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

            $quantity = (int) $validated['quantity'];
            $unitCost = (float) $validated['unit_cost'];
            $totalCost = $quantity * $unitCost;

            $record = FailedImplant::create([
                'failure_number' => $failureNumber,
                'implant_id' => $validated['implant_id'],
                'implant_buyer_id' => $validated['implant_buyer_id'] ?? null,
                'implant_sale_id' => $validated['implant_sale_id'] ?? null,
                'failure_date' => $validated['failure_date'],
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'total_cost' => $totalCost,
                'source' => $validated['source'],
                'failure_stage' => $validated['failure_stage'],
                'failure_reason' => $validated['failure_reason'],
                'patient_name' => $validated['patient_name'] ?? null,
                'tooth_number' => $validated['tooth_number'] ?? null,
                'warranty_status' => $validated['warranty_status'],
                'replacement_implant_id' => $validated['replacement_implant_id'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            // If source is stock, write off/decrement inventory immediately and log transaction
            if ($validated['source'] === 'stock') {
                $implant = Implant::findOrFail($validated['implant_id']);
                $newStock = max(0, $implant->stock_quantity - $quantity);
                $implant->update(['stock_quantity' => $newStock]);

                ImplantStockTransaction::create([
                    'implant_id' => $implant->id,
                    'type' => 'failed',
                    'quantity' => -$quantity,
                    'balance_after' => $newStock,
                    'reference_id' => $record->id,
                    'notes' => "Implant failed ({$failureNumber})",
                    'created_by' => auth()->id(),
                ]);
            }

            return $record;
        });

        return redirect()->route('failed-implants.show', $failedImplant)
            ->with('success', "Failed implant record {$failedImplant->failure_number} logged successfully and calculated separately.");
    }

    public function show(FailedImplant $failedImplant)
    {
        $failedImplant->load(['implant', 'buyer', 'sale', 'replacementImplant', 'createdBy']);

        return view('failed-implants.show', compact('failedImplant'));
    }

    public function edit(FailedImplant $failedImplant)
    {
        $failedImplant->load(['implant', 'buyer', 'sale']);
        $implants = Implant::where('status', 'active')->orderBy('name')->get();

        return view('failed-implants.edit', compact('failedImplant', 'implants'));
    }

    public function update(Request $request, FailedImplant $failedImplant)
    {
        $validated = $request->validate([
            'failure_stage' => 'required|string',
            'failure_reason' => 'required|string',
            'patient_name' => 'nullable|string|max:255',
            'tooth_number' => 'nullable|string|max:50',
            'warranty_status' => 'required|string',
            'replacement_implant_id' => 'nullable|exists:implants,id',
            'notes' => 'nullable|string',
        ]);

        $failedImplant->update($validated);

        return redirect()->route('failed-implants.show', $failedImplant)
            ->with('success', "Failed implant record {$failedImplant->failure_number} updated successfully.");
    }

    public function destroy(FailedImplant $failedImplant)
    {
        DB::transaction(function () use ($failedImplant) {
            // If the failure was from stock, restore the stock count
            if ($failedImplant->source === 'stock') {
                $implant = $failedImplant->implant;
                if ($implant) {
                    $newStock = $implant->stock_quantity + $failedImplant->quantity;
                    $implant->update(['stock_quantity' => $newStock]);

                    ImplantStockTransaction::create([
                        'implant_id' => $implant->id,
                        'type' => 'adjustment',
                        'quantity' => $failedImplant->quantity,
                        'balance_after' => $newStock,
                        'reference_id' => $failedImplant->id,
                        'notes' => "Reversed failure record deletion ({$failedImplant->failure_number})",
                        'created_by' => auth()->id(),
                    ]);
                }
            }

            $failedImplant->delete();
        });

        return redirect()->route('failed-implants.index')
            ->with('success', 'Failed implant record deleted and inventory restored if applicable.');
    }
}
