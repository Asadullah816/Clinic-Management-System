<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Medicine;
use App\Models\MedicineUsage;
use App\Models\Patient;
use App\Models\PatientTreatment;
use App\Models\PatientTreatmentMedicine;
use App\Models\Payment;
use App\Models\Treatment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PatientTreatmentController extends Controller
{
    private function rules(): array
    {
        return [
            'patient_id' => 'required|exists:patients,id',
            'treatment_id' => 'nullable|exists:treatments,id',
            'procedure_name' => 'nullable|string|max:255',
            'appointment_id' => 'nullable|exists:appointments,id',
            'procedures' => 'nullable|array',
            'procedures.*.treatment_id' => 'nullable|exists:treatments,id',
            'procedures.*.name' => 'nullable|string|max:255',
            'procedures.*.price' => 'nullable|numeric|min:0',
            'procedures.*.notes' => 'nullable|string|max:255',
            'treatment_date' => 'required|date',
            'price' => 'required|numeric|min:0',
            'discount_type' => 'nullable|in:fixed,percentage',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'discount' => 'nullable|numeric|min:0',
            'medicine_discount_type' => 'nullable|in:fixed,percentage',
            'medicine_discount_percentage' => 'nullable|numeric|min:0|max:100',
            'medicine_discount' => 'nullable|numeric|min:0',
            'medicines' => 'nullable|array',
            'medicines.*.id' => 'required|exists:medicines,id',
            'medicines.*.quantity' => 'required|integer|min:1',
            'medicines.*.unit_price' => 'required|numeric|min:0',
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

    private function getMedicinesPayload()
    {
        return Medicine::with('category')
            ->where('status', Medicine::STATUS_ACTIVE)
            ->orderBy('name')
            ->get()
            ->map(function ($med) {
                return [
                    'id' => $med->id,
                    'name' => $med->name,
                    'generic_name' => $med->generic_name,
                    'category' => $med->category->name ?? 'General',
                    'selling_price' => (float) $med->selling_price,
                    'stock_quantity' => (int) $med->stock_quantity,
                    'unit' => $med->unit ?? 'unit',
                    'image_url' => $med->image_url,
                    'is_expired' => $med->isExpired(),
                ];
            });
    }

    private function getPatientAppointmentsMap(): array
    {
        $appointments = Appointment::with('treatment')
            ->whereIn('status', [Appointment::STATUS_SCHEDULED, Appointment::STATUS_COMPLETED])
            ->orderByDesc('appointment_date')
            ->orderByDesc('appointment_time')
            ->get();

        $patientAppointments = [];
        foreach ($appointments as $appt) {
            $pid = (int) $appt->patient_id;
            // Prioritize scheduled appointments, otherwise record the latest appointment
            if (! isset($patientAppointments[$pid]) || $appt->status === Appointment::STATUS_SCHEDULED) {
                $patientAppointments[$pid] = [
                    'appointment_id' => $appt->id,
                    'treatment_id' => $appt->treatment_id,
                    'treatment_name' => $appt->treatment?->name ?? 'Walk-in / Consultation',
                    'treatment_price' => (float) ($appt->treatment?->price ?? 0),
                    'appointment_date' => $appt->appointment_date?->format('d M Y'),
                    'appointment_time' => $appt->appointment_time,
                    'status' => $appt->status,
                ];
            }
        }

        return $patientAppointments;
    }

    public function index(Request $request)
    {
        $patientTreatments = PatientTreatment::with(['patient', 'treatment', 'treatmentMedicines.medicine'])
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
        $appointmentId = $request->query('appointment_id');
        $patientId = $request->query('patient_id');
        $treatmentId = $request->query('treatment_id');
        $patientAppointments = $this->getPatientAppointmentsMap();

        if ($appointmentId) {
            $appointment = Appointment::with('treatment')->find($appointmentId);
            if ($appointment) {
                $patientId = $patientId ?: $appointment->patient_id;
                $treatmentId = $treatmentId ?: $appointment->treatment_id;
            }
        } elseif ($patientId && ! $treatmentId && isset($patientAppointments[(int) $patientId])) {
            $treatmentId = $patientAppointments[(int) $patientId]['treatment_id'];
            $appointmentId = $patientAppointments[(int) $patientId]['appointment_id'];
        }

        $treatment = $treatmentId ? Treatment::find($treatmentId) : null;
        $price = $treatment?->price ?? ($request->filled('price') ? (float) $request->price : null);

        return view('patient-treatments.create', [
            'patients' => Patient::orderBy('first_name')->orderBy('last_name')->get(),
            'treatments' => Treatment::where('status', Treatment::STATUS_ACTIVE)->orderBy('name')->get(),
            'medicines' => $this->getMedicinesPayload(),
            'patientId' => $patientId,
            'treatmentId' => $treatmentId,
            'appointmentId' => $appointmentId,
            'price' => $price,
            'patientAppointments' => $patientAppointments,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        if (empty($validated['treatment_id']) && empty($validated['procedure_name']) && empty($validated['procedures'])) {
            return back()
                ->withErrors(['treatment_id' => 'Please select a catalog treatment or enter a procedure description.'])
                ->withInput();
        }

        // Resolve procedure names, primary treatment_id, and procedure notes
        $procedureNames = [];
        $procedureSummaries = [];
        $primaryTreatmentId = $validated['treatment_id'] ?? null;
        $calculatedProcedurePrice = 0.0;

        if (! empty($validated['procedures']) && is_array($validated['procedures'])) {
            foreach ($validated['procedures'] as $proc) {
                $pName = trim($proc['name'] ?? '');
                $pId = ! empty($proc['treatment_id']) ? (int) $proc['treatment_id'] : null;
                $pPrice = isset($proc['price']) ? (float) $proc['price'] : 0.0;
                $pNotes = trim($proc['notes'] ?? '');

                if (! $pName && $pId) {
                    $pName = Treatment::find($pId)?->name;
                }

                if ($pName) {
                    $procedureNames[] = $pName;
                    $calculatedProcedurePrice += $pPrice;
                    if (! $primaryTreatmentId && $pId) {
                        $primaryTreatmentId = $pId;
                    }
                    $summary = $pName.' (PKR '.number_format($pPrice, 2).')';
                    if ($pNotes) {
                        $summary .= ' ['.$pNotes.']';
                    }
                    $procedureSummaries[] = $summary;
                }
            }
        }

        $procedureTitle = ! empty($procedureNames)
            ? implode(' + ', $procedureNames)
            : ($validated['procedure_name'] ?? null);

        if (! $procedureTitle && $primaryTreatmentId) {
            $primaryTreatment = Treatment::find($primaryTreatmentId);
            $procedureTitle = $primaryTreatment?->name;
        }

        if (! $procedureTitle) {
            $procedureTitle = 'Dental Procedure';
        }

        $treatmentPrice = (float) $validated['price'];
        if (! empty($procedureSummaries) && $calculatedProcedurePrice > 0 && abs($treatmentPrice - $calculatedProcedurePrice) < 0.01) {
            $treatmentPrice = $calculatedProcedurePrice;
        }

        $clinicalNotes = $validated['notes'] ?? '';
        if (! empty($procedureSummaries) && count($procedureSummaries) > 1) {
            $breakdownText = 'Procedures: '.implode(' | ', $procedureSummaries);
            $clinicalNotes = $clinicalNotes ? ($breakdownText."\n".$clinicalNotes) : $breakdownText;
        }

        $discountType = $validated['discount_type'] ?? 'fixed';
        if ($discountType === 'percentage' && isset($validated['discount_percentage']) && $validated['discount_percentage'] !== '') {
            $treatmentDiscount = round($treatmentPrice * ((float) $validated['discount_percentage'] / 100), 2);
        } else {
            $treatmentDiscount = (float) ($validated['discount'] ?? 0);
        }

        if ($treatmentDiscount > $treatmentPrice) {
            return back()
                ->withErrors(['discount' => 'Procedure discount cannot exceed procedure fee of '.number_format($treatmentPrice, 2).'.'])
                ->withInput();
        }

        $treatmentNet = max(0, $treatmentPrice - $treatmentDiscount);

        // Process medicines and validate stock
        $medicinesData = [];
        $medicinesSubtotal = 0.0;

        if (! empty($validated['medicines'])) {
            foreach ($validated['medicines'] as $medInput) {
                $med = Medicine::find($medInput['id']);
                if (! $med) {
                    continue;
                }

                $qty = (int) $medInput['quantity'];
                $unitPrice = (float) $medInput['unit_price'];

                if ($med->isExpired()) {
                    return back()
                        ->withErrors(['medicines' => '"'.$med->name.'" is expired and cannot be used on patients.'])
                        ->withInput();
                }

                if ($qty > (int) $med->stock_quantity) {
                    return back()
                        ->withErrors(['medicines' => 'Insufficient stock for "'.$med->name.'". Only '.$med->stock_quantity.' '.($med->unit ?? 'units').' available.'])
                        ->withInput();
                }

                $lineTotal = $qty * $unitPrice;
                $medicinesSubtotal += $lineTotal;

                $medicinesData[] = [
                    'model' => $med,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ];
            }
        }

        $medicineDiscountType = $validated['medicine_discount_type'] ?? 'fixed';
        if ($medicineDiscountType === 'percentage' && isset($validated['medicine_discount_percentage']) && $validated['medicine_discount_percentage'] !== '') {
            $medicineDiscount = round($medicinesSubtotal * ((float) $validated['medicine_discount_percentage'] / 100), 2);
        } else {
            $medicineDiscount = (float) ($validated['medicine_discount'] ?? 0);
        }

        if ($medicineDiscount > $medicinesSubtotal) {
            return back()
                ->withErrors(['medicine_discount' => 'Medicine discount cannot exceed medicines subtotal of '.number_format($medicinesSubtotal, 2).'.'])
                ->withInput();
        }

        $medicineTotal = max(0, $medicinesSubtotal - $medicineDiscount);
        $grandTotal = max(0, $treatmentNet + $medicineTotal);

        // Paid amount defaults to grand total if omitted or empty
        $paidAmount = isset($validated['paid_amount']) && $validated['paid_amount'] !== '' && $validated['paid_amount'] !== null
            ? (float) $validated['paid_amount']
            : $grandTotal;

        if ($paidAmount > $grandTotal) {
            return back()
                ->withErrors(['paid_amount' => 'Paid amount cannot exceed grand total of '.number_format($grandTotal, 2).'.'])
                ->withInput();
        }

        $dueAmount = max(0, $grandTotal - $paidAmount);
        $invoiceNumber = null;

        DB::transaction(function () use (
            $validated, $treatmentPrice, $treatmentDiscount, $treatmentNet,
            $primaryTreatmentId, $procedureTitle, $clinicalNotes,
            $medicinesData, $medicinesSubtotal, $medicineDiscount, $medicineTotal,
            $grandTotal, $paidAmount, $dueAmount, &$invoiceNumber
        ) {
            // 1. Create Patient Treatment record
            $patientTreatment = PatientTreatment::create([
                'patient_id' => $validated['patient_id'],
                'treatment_id' => $primaryTreatmentId,
                'procedure_name' => $procedureTitle,
                'treatment_date' => $validated['treatment_date'],
                'price' => $treatmentPrice,
                'discount' => $treatmentDiscount,
                'medicine_price' => $medicinesSubtotal,
                'medicine_discount' => $medicineDiscount,
                'medicine_total' => $medicineTotal,
                'total_amount' => $grandTotal,
                'notes' => $clinicalNotes ?: null,
                'created_by' => auth()->id(),
            ]);

            // 2. Complete appointment if linked
            if (! empty($validated['appointment_id'])) {
                $appointment = Appointment::where('id', $validated['appointment_id'])
                    ->where('patient_id', $validated['patient_id'])
                    ->first();
                if ($appointment && $appointment->status === Appointment::STATUS_SCHEDULED) {
                    $appointment->update(['status' => Appointment::STATUS_COMPLETED]);
                }
            }

            // 3. Deduct stock, create PatientTreatmentMedicine and MedicineUsage records
            $medSummaryList = [];
            foreach ($medicinesData as $item) {
                $med = $item['model'];
                $qty = $item['quantity'];

                // Deduct stock
                $med->decrement('stock_quantity', $qty);

                // Save treatment medicine row
                PatientTreatmentMedicine::create([
                    'patient_treatment_id' => $patientTreatment->id,
                    'medicine_id' => $med->id,
                    'quantity' => $qty,
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['line_total'],
                ]);

                // Track in Medicine Usage ledger for clinic audit trail
                MedicineUsage::create([
                    'patient_id' => $validated['patient_id'],
                    'treatment_id' => $primaryTreatmentId,
                    'medicine_id' => $med->id,
                    'quantity' => $qty,
                    'usage_date' => $validated['treatment_date'],
                    'notes' => 'Treatment #'.$patientTreatment->id.' — '.$procedureTitle,
                    'used_by' => auth()->id(),
                ]);

                $medSummaryList[] = $qty.'x '.$med->name;
            }

            // 4. Generate unique invoice number
            $invoiceNumber = $this->generateInvoiceNumber();

            // 5. Invoice status
            $invoiceStatus = $dueAmount <= 0
                ? Invoice::STATUS_PAID
                : ($paidAmount > 0 ? Invoice::STATUS_PARTIAL : Invoice::STATUS_PENDING);

            // Construct invoice notes detailing Treatment & Medicines breakdown
            $invoiceNotes = 'Treatment: '.$procedureTitle.' (Net: '.number_format($treatmentNet, 2).')';
            if (! empty($medSummaryList)) {
                $invoiceNotes .= ' | Medicines: '.implode(', ', $medSummaryList).' (Net: '.number_format($medicineTotal, 2).')';
            }
            if (! empty($clinicalNotes)) {
                $invoiceNotes .= ' — '.$clinicalNotes;
            }

            // 6. Create Invoice
            $invoice = Invoice::create([
                'patient_id' => $validated['patient_id'],
                'invoice_number' => $invoiceNumber,
                'invoice_date' => $validated['treatment_date'],
                'subtotal' => $treatmentPrice + $medicinesSubtotal,
                'discount' => $treatmentDiscount + $medicineDiscount,
                'total_amount' => $grandTotal,
                'paid_amount' => $paidAmount,
                'due_amount' => $dueAmount,
                'status' => $invoiceStatus,
                'notes' => $invoiceNotes,
                'created_by' => auth()->id(),
            ]);

            // 7. Create Payment record if paid amount > 0
            if ($paidAmount > 0) {
                Payment::create([
                    'patient_id' => $validated['patient_id'],
                    'invoice_id' => $invoice->id,
                    'payment_date' => $validated['treatment_date'],
                    'amount' => $paidAmount,
                    'payment_method' => $validated['payment_method'] ?? Payment::METHOD_CASH,
                    'reference' => $validated['payment_reference'] ?? null,
                    'notes' => 'Payment for '.$procedureTitle.' ('.$invoiceNumber.')',
                    'received_by' => auth()->id(),
                ]);
            }
        });

        $statusNote = $dueAmount > 0
            ? ' (Paid: '.number_format($paidAmount, 2).', Due: '.number_format($dueAmount, 2).')'
            : ' (Fully Paid)';

        $successMessage = 'Treatment recorded successfully with medicines and stock updated. Invoice '.$invoiceNumber.' created'.$statusNote.'.';

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
        $patientTreatment->load(['treatmentMedicines.medicine']);

        return view('patient-treatments.edit', [
            'patientTreatment' => $patientTreatment,
            'patients' => Patient::orderBy('first_name')->orderBy('last_name')->get(),
            'treatments' => Treatment::where('status', Treatment::STATUS_ACTIVE)->orderBy('name')->get(),
            'medicines' => $this->getMedicinesPayload(),
            'patientId' => null,
            'treatmentId' => null,
            'appointmentId' => null,
            'price' => $patientTreatment->price,
            'patientAppointments' => $this->getPatientAppointmentsMap(),
        ]);
    }

    public function update(Request $request, PatientTreatment $patientTreatment)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'treatment_id' => 'nullable|exists:treatments,id',
            'procedure_name' => 'nullable|string|max:255',
            'procedures' => 'nullable|array',
            'procedures.*.treatment_id' => 'nullable|exists:treatments,id',
            'procedures.*.name' => 'nullable|string|max:255',
            'procedures.*.price' => 'nullable|numeric|min:0',
            'procedures.*.notes' => 'nullable|string|max:255',
            'treatment_date' => 'required|date',
            'price' => 'required|numeric|min:0',
            'discount_type' => 'nullable|in:fixed,percentage',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'discount' => 'nullable|numeric|min:0',
            'medicine_discount_type' => 'nullable|in:fixed,percentage',
            'medicine_discount_percentage' => 'nullable|numeric|min:0|max:100',
            'medicine_discount' => 'nullable|numeric|min:0',
            'medicines' => 'nullable|array',
            'medicines.*.id' => 'required|exists:medicines,id',
            'medicines.*.quantity' => 'required|integer|min:1',
            'medicines.*.unit_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:2000',
        ]);

        if (empty($validated['treatment_id']) && empty($validated['procedure_name']) && empty($validated['procedures'])) {
            return back()
                ->withErrors(['treatment_id' => 'Please select a catalog treatment or enter a procedure description.'])
                ->withInput();
        }

        // Resolve procedure names, primary treatment_id, and procedure notes
        $procedureNames = [];
        $procedureSummaries = [];
        $primaryTreatmentId = $validated['treatment_id'] ?? null;
        $calculatedProcedurePrice = 0.0;

        if (! empty($validated['procedures']) && is_array($validated['procedures'])) {
            foreach ($validated['procedures'] as $proc) {
                $pName = trim($proc['name'] ?? '');
                $pId = ! empty($proc['treatment_id']) ? (int) $proc['treatment_id'] : null;
                $pPrice = isset($proc['price']) ? (float) $proc['price'] : 0.0;
                $pNotes = trim($proc['notes'] ?? '');

                if (! $pName && $pId) {
                    $pName = Treatment::find($pId)?->name;
                }

                if ($pName) {
                    $procedureNames[] = $pName;
                    $calculatedProcedurePrice += $pPrice;
                    if (! $primaryTreatmentId && $pId) {
                        $primaryTreatmentId = $pId;
                    }
                    $summary = $pName.' (PKR '.number_format($pPrice, 2).')';
                    if ($pNotes) {
                        $summary .= ' ['.$pNotes.']';
                    }
                    $procedureSummaries[] = $summary;
                }
            }
        }

        $procedureTitle = ! empty($procedureNames)
            ? implode(' + ', $procedureNames)
            : ($validated['procedure_name'] ?? null);

        if (! $procedureTitle && $primaryTreatmentId) {
            $primaryTreatment = Treatment::find($primaryTreatmentId);
            $procedureTitle = $primaryTreatment?->name;
        }

        if (! $procedureTitle) {
            $procedureTitle = 'Dental Procedure';
        }

        $treatmentPrice = (float) $validated['price'];
        if (! empty($procedureSummaries) && $calculatedProcedurePrice > 0 && abs($treatmentPrice - $calculatedProcedurePrice) < 0.01) {
            $treatmentPrice = $calculatedProcedurePrice;
        }

        $clinicalNotes = $validated['notes'] ?? '';
        if (! empty($procedureSummaries) && count($procedureSummaries) > 1) {
            $breakdownText = 'Procedures: '.implode(' | ', $procedureSummaries);
            $clinicalNotes = $clinicalNotes ? ($breakdownText."\n".$clinicalNotes) : $breakdownText;
        }

        $discountType = $validated['discount_type'] ?? 'fixed';
        if ($discountType === 'percentage' && isset($validated['discount_percentage']) && $validated['discount_percentage'] !== '') {
            $treatmentDiscount = round($treatmentPrice * ((float) $validated['discount_percentage'] / 100), 2);
        } else {
            $treatmentDiscount = (float) ($validated['discount'] ?? 0);
        }

        if ($treatmentDiscount > $treatmentPrice) {
            return back()
                ->withErrors(['discount' => 'Procedure discount cannot exceed procedure fee of '.number_format($treatmentPrice, 2).'.'])
                ->withInput();
        }

        $treatmentNet = max(0, $treatmentPrice - $treatmentDiscount);

        DB::transaction(function () use (
            $validated, $patientTreatment, $treatmentPrice, $treatmentDiscount, $treatmentNet,
            $primaryTreatmentId, $procedureTitle, $clinicalNotes
        ) {
            // 1. Restore previous stock for this treatment's medicines before applying new ones
            $patientTreatment->load(['treatmentMedicines.medicine']);
            foreach ($patientTreatment->treatmentMedicines as $tm) {
                if ($tm->medicine) {
                    $tm->medicine->increment('stock_quantity', $tm->quantity);
                }
            }
            $patientTreatment->treatmentMedicines()->delete();
            MedicineUsage::where('patient_id', $patientTreatment->patient_id)
                ->where('notes', 'like', '%Treatment #'.$patientTreatment->id.'%')
                ->delete();

            // 2. Validate new medicines stock
            $medicinesData = [];
            $medicinesSubtotal = 0.0;

            if (! empty($validated['medicines'])) {
                foreach ($validated['medicines'] as $medInput) {
                    $med = Medicine::find($medInput['id']);
                    if (! $med) {
                        continue;
                    }

                    $qty = (int) $medInput['quantity'];
                    $unitPrice = (float) $medInput['unit_price'];

                    if ($med->isExpired()) {
                        throw ValidationException::withMessages([
                            'medicines' => '"'.$med->name.'" is expired and cannot be used.',
                        ]);
                    }

                    if ($qty > (int) $med->stock_quantity) {
                        throw ValidationException::withMessages([
                            'medicines' => 'Insufficient stock for "'.$med->name.'". Only '.$med->stock_quantity.' available.',
                        ]);
                    }

                    $lineTotal = $qty * $unitPrice;
                    $medicinesSubtotal += $lineTotal;

                    $medicinesData[] = [
                        'model' => $med,
                        'quantity' => $qty,
                        'unit_price' => $unitPrice,
                        'line_total' => $lineTotal,
                    ];
                }
            }

            $medicineDiscountType = $validated['medicine_discount_type'] ?? 'fixed';
            if ($medicineDiscountType === 'percentage' && isset($validated['medicine_discount_percentage']) && $validated['medicine_discount_percentage'] !== '') {
                $medicineDiscount = round($medicinesSubtotal * ((float) $validated['medicine_discount_percentage'] / 100), 2);
            } else {
                $medicineDiscount = (float) ($validated['medicine_discount'] ?? 0);
            }

            if ($medicineDiscount > $medicinesSubtotal) {
                throw ValidationException::withMessages([
                    'medicine_discount' => 'Medicine discount cannot exceed medicines subtotal of '.number_format($medicinesSubtotal, 2).'.',
                ]);
            }

            $medicineTotal = max(0, $medicinesSubtotal - $medicineDiscount);
            $grandTotal = max(0, $treatmentNet + $medicineTotal);

            // 3. Deduct stock and record treatment medicines + usage
            foreach ($medicinesData as $item) {
                $med = $item['model'];
                $qty = $item['quantity'];

                $med->decrement('stock_quantity', $qty);

                PatientTreatmentMedicine::create([
                    'patient_treatment_id' => $patientTreatment->id,
                    'medicine_id' => $med->id,
                    'quantity' => $qty,
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['line_total'],
                ]);

                MedicineUsage::create([
                    'patient_id' => $validated['patient_id'],
                    'treatment_id' => $primaryTreatmentId,
                    'medicine_id' => $med->id,
                    'quantity' => $qty,
                    'usage_date' => $validated['treatment_date'],
                    'notes' => 'Treatment #'.$patientTreatment->id.' — '.$procedureTitle,
                    'used_by' => auth()->id(),
                ]);
            }

            // 4. Update the PatientTreatment record
            $patientTreatment->update([
                'patient_id' => $validated['patient_id'],
                'treatment_id' => $primaryTreatmentId,
                'procedure_name' => $procedureTitle,
                'treatment_date' => $validated['treatment_date'],
                'price' => $treatmentPrice,
                'discount' => $treatmentDiscount,
                'medicine_price' => $medicinesSubtotal,
                'medicine_discount' => $medicineDiscount,
                'medicine_total' => $medicineTotal,
                'total_amount' => $grandTotal,
                'notes' => $clinicalNotes ?: null,
            ]);
        });

        return redirect()
            ->route('patient-treatments.index')
            ->with('success', 'Treatment record and medicines updated successfully.');
    }

    public function destroy(Request $request, PatientTreatment $patientTreatment)
    {
        DB::transaction(function () use ($patientTreatment) {
            // Restore stock for all medicines used in this treatment
            foreach ($patientTreatment->treatmentMedicines as $tm) {
                if ($tm->medicine) {
                    $tm->medicine->increment('stock_quantity', $tm->quantity);
                }
            }

            // Clean up corresponding medicine usage records
            MedicineUsage::where('patient_id', $patientTreatment->patient_id)
                ->where('notes', 'like', '%Treatment #'.$patientTreatment->id.'%')
                ->delete();

            $patientTreatment->delete();
        });

        if ($request->query('redirect') === 'patient') {
            return redirect()
                ->to(route('patients.show', $patientTreatment->patient_id).'#treatments')
                ->with('success', 'Treatment record deleted and product stock restored successfully.');
        }

        return redirect()
            ->route('patient-treatments.index')
            ->with('success', 'Treatment record deleted and product stock restored successfully.');
    }
}
