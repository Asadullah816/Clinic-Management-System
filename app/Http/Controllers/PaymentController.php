<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    private function rules(): array
    {
        return [
            'payment_date'   => 'required|date',
            'amount'         => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,bank,card,online,other',
            'reference'      => 'nullable|string|max:255',
            'notes'          => 'nullable|string|max:2000',
        ];
    }

    public function index(Request $request)
    {
        $payments = Payment::with(['patient', 'invoice', 'receivedBy'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->whereHas('patient', function ($p) use ($search) {
                        $p->where('first_name', 'like', "%{$search}%")
                          ->orWhere('last_name', 'like', "%{$search}%")
                          ->orWhere('patient_number', 'like', "%{$search}%");
                    })->orWhereHas('invoice', function ($i) use ($search) {
                        $i->where('invoice_number', 'like', "%{$search}%");
                    });
                });
            })
            ->when($request->filled('payment_method'), function ($query) use ($request) {
                $query->where('payment_method', $request->payment_method);
            })
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('payments.index', [
            'payments' => $payments,
        ]);
    }

    public function create(Request $request)
    {
        $invoiceId    = $request->query('invoice_id');
        $lockedInvoice = $invoiceId ? Invoice::with('patient')->find($invoiceId) : null;

        // Coming from an invoice that is already settled → nowhere to pay
        if ($lockedInvoice && $lockedInvoice->due_amount <= 0) {
            return redirect()
                ->route('invoices.show', $lockedInvoice)
                ->with('error', 'This invoice is already fully paid.');
        }

        // Standalone mode: dropdown only shows invoices that still have a due amount
        $invoices = Invoice::with('patient')
            ->where('due_amount', '>', 0)
            ->orderByDesc('invoice_date')
            ->orderByDesc('id')
            ->get();

        return view('payments.create', [
            'payment'      => null,
            'invoices'     => $invoices,
            'lockedInvoice'=> $lockedInvoice,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate(
            $this->rules() + ['invoice_id' => 'required|exists:invoices,id']
        );

        $invoice = Invoice::findOrFail($validated['invoice_id']);

        // Financial guard 1: the invoice must still have a due amount
        if ($invoice->due_amount <= 0) {
            return back()
                ->withErrors(['invoice_id' => 'This invoice is already fully paid.'])
                ->withInput();
        }

        // Financial guard 2: payment may not exceed the remaining due
        if ((float) $validated['amount'] > (float) $invoice->due_amount) {
            return back()
                ->withErrors(['amount' =>
                    'The amount cannot exceed the remaining due of ' .
                    number_format($invoice->due_amount, 2) . '.'])
                ->withInput();
        }

        Payment::create([
            'patient_id'     => $invoice->patient_id, // derived from the invoice — one less field to get wrong
            'invoice_id'     => $invoice->id,
            'payment_date'   => $validated['payment_date'],
            'amount'         => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'reference'      => $validated['reference'] ?? null,
            'notes'          => $validated['notes'] ?? null,
            'received_by'    => auth()->id(),
        ]);

        // Update the invoice: same two lines + status method, everywhere
        $invoice->paid_amount = (float) $invoice->paid_amount + (float) $validated['amount'];
        $invoice->due_amount  = (float) $invoice->total_amount - (float) $invoice->paid_amount;
        $invoice->status      = $invoice->recalculatePaymentStatus();
        $invoice->save();

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', 'Payment of ' . number_format($validated['amount'], 2) .
                ' recorded. Invoice status: ' . $invoice->statusLabel() . '.');
    }

    public function edit(Payment $payment)
    {
        return view('payments.edit', [
            'payment'      => $payment,
            'lockedInvoice'=> $payment->invoice()->with('patient')->first(),
            'invoices'     => collect(), // the invoice is fixed after creation
        ]);
    }

    public function update(Request $request, Payment $payment)
    {
        $validated = $request->validate($this->rules());

        $invoice        = $payment->invoice;
        $paidWithoutThis = (float) $invoice->paid_amount - (float) $payment->amount;
        $newPaidTotal    = $paidWithoutThis + (float) $validated['amount'];

        // Guard: this invoice's total can never be exceeded
        if ($newPaidTotal > (float) $invoice->total_amount) {
            return back()
                ->withErrors(['amount' =>
                    'The maximum allowed for this payment is ' .
                    number_format((float) $invoice->total_amount - $paidWithoutThis, 2) . '.'])
                ->withInput();
        }

        $invoice->paid_amount = $newPaidTotal;
        $invoice->due_amount  = (float) $invoice->total_amount - $newPaidTotal;
        $invoice->status      = $invoice->recalculatePaymentStatus();
        $invoice->save();

        $payment->update($validated); // received_by stays — who received the money doesn't change

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', 'Payment updated successfully.');
    }

    public function destroy(Payment $payment)
    {
        $invoice = $payment->invoice;

        // Reverse this payment's effect on the invoice, then delete
        $invoice->paid_amount = (float) $invoice->paid_amount - (float) $payment->amount;
        $invoice->due_amount  = (float) $invoice->total_amount - (float) $invoice->paid_amount;
        $invoice->status      = $invoice->recalculatePaymentStatus();
        $invoice->save();

        $payment->delete();

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', 'Payment deleted. The invoice amounts have been updated.');
    }

    /**
     * All invoices that still have money owed.
     */
    public function outstanding()
    {
        $invoices = Invoice::with('patient')
            ->where('due_amount', '>', 0)
            ->orderByDesc('invoice_date')
            ->orderByDesc('id')
            ->paginate(15);

        // Total across ALL outstanding invoices (not just this page)
        $totalOutstanding = Invoice::where('due_amount', '>', 0)->sum('due_amount');

        return view('payments.outstanding', [
            'invoices'         => $invoices,
            'totalOutstanding' => $totalOutstanding,
        ]);
    }

    public function print(Payment $payment)
    {
        $payment->load(['patient', 'invoice', 'receivedBy']);

        return view('payments.print', [
            'payment' => $payment,
        ]);
    }
}
