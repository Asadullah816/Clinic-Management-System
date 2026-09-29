<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Patient;
use App\Models\PatientTreatment;
use App\Models\Payment;
use App\Models\Treatment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PatientTreatmentController extends Controller
{
    private function rules(): array
    {
        return [
            'patient_id' => 'required|exists:patients,id',
            'treatment_id' => 'required|exists:treatments,id',
            'treatment_date' => 'required|date',
            'price' => 'required|numeric|min:0',
            // lte:price → discount may not exceed the price (total stays >= 0)
            'discount' => 'nullable|numeric|min:0|lte:price',
            'paid_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|in:cash,bank,card,online,other',
            'payment_reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:2000',
        ];
    }

    private function generateInvoiceNumber(): string
    {
        $nextId = (Invoice::max('id') ?? 0) + 1;
        $invoiceNumber = 'INV-'.str_pad($nextId, 4, '0', STR_PAD_LEFT);

        while (Invoice::where('invoice_number', $invoiceNumber)->exists()) {
            $nextId++;
            $invoiceNumber = 'INV-'.str_pad($nextId, 4, '0', STR_PAD_LEFT);
        }

        return $invoiceNumber;
    }

    public function index(Request $request)
    {
        $patientTreatments = PatientTreatment::with(['patient', 'treatment'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->whereHas('patient', function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('patient_number', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('treatment_date')
            ->paginate(10)
            ->withQueryString();

        return view('patient-treatments.index', [
            'patientTreatments' => $patientTreatments,
        ]);
    }

    public function create(Request $request)
    {
        // Prefill support: ?patient_id=X&treatment_id=Y (from profile tab / completed appointment)
        $treatment = $request->filled('treatment_id')
            ? Treatment::find($request->treatment_id)
            : null;

        return view('patient-treatments.create', [
            'patients' => Patient::orderBy('first_name')->orderBy('last_name')->get(),
            'treatments' => Treatment::where('status', Treatment::STATUS_ACTIVE)->orderBy('name')->get(),
            'patientId' => $request->query('patient_id'),
            'treatmentId' => $request->query('treatment_id'),
            'price' => $treatment?->price, // snapshot price prefill
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        $discount = (float) ($validated['discount'] ?? 0);
        $price = (float) $validated['price'];
        $totalAmount = max(0, $price - $discount);

        // Paid amount defaults to total amount if omitted or null
        $paidAmount = isset($validated['paid_amount']) && $validated['paid_amount'] !== '' && $validated['paid_amount'] !== null
            ? (float) $validated['paid_amount']
            : $totalAmount;

        // Guard: paid amount cannot exceed total amount
        if ($paidAmount > $totalAmount) {
            return back()
                ->withErrors(['paid_amount' => 'Paid amount cannot exceed total amount of '.number_format($totalAmount, 2).'.'])
                ->withInput();
        }

        $dueAmount = max(0, $totalAmount - $paidAmount);
        $invoiceNumber = null;

        DB::transaction(function () use ($validated, $price, $discount, $totalAmount, $paidAmount, $dueAmount, &$invoiceNumber) {
            // 1. Create Patient Treatment record
            PatientTreatment::create([
                'patient_id' => $validated['patient_id'],
                'treatment_id' => $validated['treatment_id'],
                'treatment_date' => $validated['treatment_date'],
                'price' => $price,
                'discount' => $discount,
                'total_amount' => $totalAmount,
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            $treatment = Treatment::find($validated['treatment_id']);
            $treatmentName = $treatment?->name ?? 'Treatment';

            // 2. Generate unique invoice number
            $invoiceNumber = $this->generateInvoiceNumber();

            // 3. Determine invoice status derived from paid vs due
            $invoiceStatus = $dueAmount <= 0
                ? Invoice::STATUS_PAID
                : ($paidAmount > 0 ? Invoice::STATUS_PARTIAL : Invoice::STATUS_PENDING);

            // 4. Create Invoice
            $invoice = Invoice::create([
                'patient_id' => $validated['patient_id'],
                'invoice_number' => $invoiceNumber,
                'invoice_date' => $validated['treatment_date'],
                'subtotal' => $price,
                'discount' => $discount,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'due_amount' => $dueAmount,
                'status' => $invoiceStatus,
                'notes' => 'Treatment: '.$treatmentName.(! empty($validated['notes']) ? ' — '.$validated['notes'] : ''),
                'created_by' => auth()->id(),
            ]);

            // 5. Create Payment record if money was actually paid
            if ($paidAmount > 0) {
                Payment::create([
                    'patient_id' => $validated['patient_id'],
                    'invoice_id' => $invoice->id,
                    'payment_date' => $validated['treatment_date'],
                    'amount' => $paidAmount,
                    'payment_method' => $validated['payment_method'] ?? Payment::METHOD_CASH,
                    'reference' => $validated['payment_reference'] ?? null,
                    'notes' => 'Payment for '.$treatmentName.' ('.$invoiceNumber.')',
                    'received_by' => auth()->id(),
                ]);
            }
        });

        $statusNote = $dueAmount > 0
            ? ' (Paid: '.number_format($paidAmount, 2).', Due: '.number_format($dueAmount, 2).')'
            : ' (Fully Paid)';

        $successMessage = 'Treatment recorded successfully. Invoice '.$invoiceNumber.' created'.$statusNote.'.';

        if ($request->input('redirect') === 'patient') {
            return redirect()
                ->to(route('patients.show', $validated['patient_id']).'#treatments')
                ->with('success', $successMessage);
        }

        return redirect()
            ->route('patient-treatments.index')
            ->with('success', $successMessage);
    }

    public function edit(PatientTreatment $patientTreatment)
    {
        return view('patient-treatments.edit', [
            'patientTreatment' => $patientTreatment,
            'patients' => Patient::orderBy('first_name')->orderBy('last_name')->get(),
            'treatments' => Treatment::where('status', Treatment::STATUS_ACTIVE)->orderBy('name')->get(),
            'patientId' => null,
            'treatmentId' => null,
            'price' => $patientTreatment->price,
        ]);
    }

    public function update(Request $request, PatientTreatment $patientTreatment)
    {
        $validated = $request->validate($this->rules());

        $discount = $validated['discount'] ?? 0;

        $patientTreatment->update([
            'patient_id' => $validated['patient_id'],
            'treatment_id' => $validated['treatment_id'],
            'treatment_date' => $validated['treatment_date'],
            'price' => $validated['price'],
            'discount' => $discount,
            'total_amount' => $validated['price'] - $discount,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()
            ->route('patient-treatments.index')
            ->with('success', 'Treatment record updated successfully.');
    }

    public function destroy(Request $request, PatientTreatment $patientTreatment)
    {
        $patientTreatment->delete();

        // Returning to the patient profile tab when deleted from there
        if ($request->query('redirect') === 'patient') {
            return redirect()
                ->to(route('patients.show', $patientTreatment->patient_id).'#treatments')
                ->with('success', 'Treatment record deleted successfully.');
        }

        return redirect()
            ->route('patient-treatments.index')
            ->with('success', 'Treatment record deleted successfully.');
    }
}
