<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Patient;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    private function rules(): array
    {
        return [
            'patient_id'   => 'required|exists:patients,id',
            'invoice_date' => 'required|date',
            'subtotal'     => 'required|numeric|min:0',
            // lte:subtotal → discount may not exceed the subtotal (total stays >= 0)
            'discount'     => 'nullable|numeric|min:0|lte:subtotal',
            'notes'        => 'nullable|string|max:2000',
        ];
    }

    public function index(Request $request)
    {
        $invoices = Invoice::with('patient')
            // Search by invoice number or patient name / number
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('invoice_number', 'like', "%{$search}%")
                      ->orWhereHas('patient', function ($p) use ($search) {
                          $p->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('patient_number', 'like', "%{$search}%");
                      });
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->orderByDesc('invoice_date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('invoices.index', [
            'invoices' => $invoices,
        ]);
    }

    public function create(Request $request)
    {
        return view('invoices.create', [
            'patients'  => Patient::orderBy('first_name')->orderBy('last_name')->get(),
            // From the patient profile: /invoices/create?patient_id=X preselects the patient
            'patientId' => $request->query('patient_id'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        $discount   = $validated['discount'] ?? 0;
        $totalAmount = $validated['subtotal'] - $discount;

        $invoice = Invoice::create([
            'patient_id'   => $validated['patient_id'],
            'invoice_date' => $validated['invoice_date'],
            'subtotal'     => $validated['subtotal'],
            'discount'     => $discount,
            'total_amount' => $totalAmount,
            'paid_amount'  => 0,
            'due_amount'   => $totalAmount,          // nothing paid yet
            'status'       => 'pending',             // recalculated just below
            'notes'        => $validated['notes'] ?? null,
            'created_by'   => auth()->id(),
        ]);

        // Edge case: a fully-discounted invoice (total 0) is instantly "paid"
        $invoice->due_amount = $invoice->total_amount;
        $invoice->status = $invoice->recalculatePaymentStatus();
        $invoice->save();

        // Generate the number from the auto-increment ID: INV-0001, INV-0002, ...
        $invoice->update([
            'invoice_number' => 'INV-' . str_pad($invoice->id, 4, '0', STR_PAD_LEFT),
        ]);

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', 'Invoice ' . $invoice->invoice_number . ' created successfully.');
    }

        public function show(Invoice $invoice)
    {
        $invoice->load(['patient', 'createdBy', 'payments.receivedBy']);

        return view('invoices.show', [
            'invoice' => $invoice,
        ]);
    }

    public function print(Invoice $invoice)
    {
        $invoice->load(['patient', 'createdBy']);

        return view('invoices.print', [
            'invoice' => $invoice,
        ]);
    }

    public function edit(Invoice $invoice)
    {
        return view('invoices.edit', [
            'invoice'  => $invoice,
            'patients' => Patient::orderBy('first_name')->orderBy('last_name')->get(),
        ]);
    }

    public function update(Request $request, Invoice $invoice)
    {
        $validated = $request->validate($this->rules());

        $discount    = $validated['discount'] ?? 0;
        $totalAmount = $validated['subtotal'] - $discount;

        // Guard: the new total may never drop below what has already been paid
        if ($totalAmount < $invoice->paid_amount) {
            return back()
                ->withErrors(['subtotal' =>
                    'The invoice total (' . number_format($totalAmount, 2) .
                    ') cannot be less than the amount already paid (' .
                    number_format($invoice->paid_amount, 2) . ').'])
                ->withInput();
        }

        $invoice->subtotal     = $validated['subtotal'];
        $invoice->discount     = $discount;
        $invoice->total_amount = $totalAmount;
        $invoice->due_amount   = $totalAmount - $invoice->paid_amount;
        $invoice->status       = $invoice->recalculatePaymentStatus();
        $invoice->patient_id   = $validated['patient_id'];
        $invoice->invoice_date = $validated['invoice_date'];
        $invoice->notes        = $validated['notes'] ?? null;
        $invoice->save();

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', 'Invoice updated successfully.');
    }

    public function destroy(Invoice $invoice)
    {
        // Guard: never delete an invoice that has money paid against it
        if ($invoice->paid_amount > 0) {
            return redirect()
                ->route('invoices.index')
                ->with('error', 'This invoice has payments recorded and cannot be deleted.');
        }

        $invoice->delete();

        return redirect()
            ->route('invoices.index')
            ->with('success', 'Invoice deleted successfully.');
    }
}
