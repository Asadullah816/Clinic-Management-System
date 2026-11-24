<?php

namespace App\Http\Controllers;

use App\Models\ImplantPayment;
use App\Models\ImplantSale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ImplantPaymentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $query = ImplantPayment::with(['sale', 'buyer', 'createdBy']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('payment_number', 'like', "%{$search}%")
                    ->orWhere('reference_number', 'like', "%{$search}%")
                    ->orWhereHas('buyer', function ($b) use ($search) {
                        $b->where('doctor_name', 'like', "%{$search}%")
                            ->orWhere('clinic_hospital_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('sale', function ($s) use ($search) {
                        $s->where('sale_number', 'like', "%{$search}%");
                    });
            });
        }

        $payments = $query->latest('payment_date')->latest('id')->paginate(15)->withQueryString();

        $stats = [
            'total_payments' => ImplantPayment::count(),
            'total_amount' => (float) ImplantPayment::sum('amount'),
        ];

        return view('implant-payments.index', compact('payments', 'stats', 'search'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'implant_sale_id' => 'required|exists:implant_sales,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string|max:50',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $sale = ImplantSale::findOrFail($validated['implant_sale_id']);

        if ($validated['amount'] > $sale->due_amount) {
            return back()->withErrors(['amount' => "Payment amount (PKR {$validated['amount']}) cannot exceed current outstanding balance (PKR {$sale->due_amount})."])->withInput();
        }

        DB::transaction(function () use ($sale, $validated) {
            $nextId = (ImplantPayment::max('id') ?? 0) + 1;
            $paymentNumber = 'IMP-PAY-'.str_pad($nextId, 4, '0', STR_PAD_LEFT);

            ImplantPayment::create([
                'payment_number' => $paymentNumber,
                'implant_sale_id' => $sale->id,
                'implant_buyer_id' => $sale->implant_buyer_id,
                'payment_date' => $validated['payment_date'],
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'reference_number' => $validated['reference_number'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            $newPaid = $sale->paid_amount + $validated['amount'];
            $newDue = max(0, $sale->total_amount - $newPaid);
            $newStatus = $newDue <= 0 ? ImplantSale::STATUS_PAID : ImplantSale::STATUS_PARTIAL;

            $sale->update([
                'paid_amount' => $newPaid,
                'due_amount' => $newDue,
                'payment_status' => $newStatus,
            ]);
        });

        return back()->with('success', 'Payment received and recorded successfully against '.$sale->sale_number.'.');
    }
}
