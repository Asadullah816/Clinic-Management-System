<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\PatientHistory;
use App\Models\PatientHistoryPayment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PatientHistoryController extends Controller
{
    private function generateRecordNumber(): string
    {
        $nextId = (PatientHistory::max('id') ?? 0) + 1;
        $recordNumber = 'HIST-'.str_pad($nextId, 4, '0', STR_PAD_LEFT);

        while (PatientHistory::where('record_number', $recordNumber)->exists()) {
            $nextId++;
            $recordNumber = 'HIST-'.str_pad($nextId, 4, '0', STR_PAD_LEFT);
        }

        return $recordNumber;
    }

    public function index(Request $request)
    {
        return redirect()->route('patients.index');
    }

    public function create(Request $request)
    {
        if ($request->filled('patient_id')) {
            return redirect()->route('patients.show', $request->patient_id);
        }

        return redirect()->route('patients.index');
    }

    public function store(Request $request)
    {
        $patientType = $request->input('patient_type', 'existing');

        // Dynamic validation rules
        $rules = [
            'patient_type' => 'required|in:existing,new',
            'visit_date' => 'required|date',
            'doctor_id' => 'nullable|exists:users,id',
            'doctor_name' => 'nullable|string|max:255',
            'procedures' => 'required|string|max:2000',
            'tooth_numbers' => 'nullable|string|max:255',
            'diagnosis' => 'nullable|string|max:2000',
            'notes' => 'nullable|string|max:2000',
            'total_cost' => 'required|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|in:cash,bank,card,online,other',
            'payment_reference' => 'nullable|string|max:255',
        ];

        if ($patientType === 'existing') {
            $rules['patient_id'] = 'required|exists:patients,id';
        } else {
            $rules['first_name'] = 'required|string|max:255';
            $rules['last_name'] = 'required|string|max:255';
            $rules['gender'] = 'required|in:male,female,other';
            $rules['phone'] = 'required|string|max:30';
            $rules['date_of_birth'] = 'nullable|date|before:today';
            $rules['address'] = 'nullable|string|max:1000';
            $rules['emergency_contact'] = 'nullable|string|max:30';
            $rules['occupation'] = 'nullable|string|max:255';
        }

        $validated = $request->validate($rules);

        $totalCost = (float) $validated['total_cost'];
        $paidAmount = isset($validated['paid_amount']) && $validated['paid_amount'] !== '' ? (float) $validated['paid_amount'] : 0.0;

        if ($paidAmount > $totalCost) {
            throw ValidationException::withMessages([
                'paid_amount' => 'Paid amount cannot exceed total previous treatment cost of PKR '.number_format($totalCost, 2).'.',
            ]);
        }

        $dueAmount = max(0, $totalCost - $paidAmount);
        $status = $dueAmount == 0 ? PatientHistory::STATUS_PAID : ($paidAmount > 0 ? PatientHistory::STATUS_PARTIAL : PatientHistory::STATUS_PENDING);

        // Resolve doctor
        $doctorId = auth()->user()->isDoctor() ? auth()->id() : ($validated['doctor_id'] ?? null);
        $doctorName = null;
        if ($doctorId) {
            $doctorName = User::find($doctorId)?->name;
        } elseif (! empty($validated['doctor_name'])) {
            $doctorName = $validated['doctor_name'];
        }

        $patientHistory = DB::transaction(function () use (
            $validated, $patientType, $totalCost, $paidAmount, $dueAmount, $status, $doctorId, $doctorName
        ) {
            // 1. Resolve Patient
            if ($patientType === 'new') {
                $patient = Patient::create([
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name'],
                    'gender' => $validated['gender'],
                    'phone' => $validated['phone'],
                    'date_of_birth' => $validated['date_of_birth'] ?? null,
                    'address' => $validated['address'] ?? null,
                    'emergency_contact' => $validated['emergency_contact'] ?? null,
                    'occupation' => $validated['occupation'] ?? null,
                    'status' => 'active',
                    'is_historical' => true,
                    'assigned_doctor_id' => $doctorId,
                ]);

                $patient->update([
                    'patient_number' => 'P-'.str_pad($patient->id, 4, '0', STR_PAD_LEFT),
                ]);
            } else {
                $patient = Patient::findOrFail($validated['patient_id']);
                $patient->update(['is_historical' => true]);
                if (! $patient->assigned_doctor_id && $doctorId) {
                    $patient->update(['assigned_doctor_id' => $doctorId]);
                }
            }

            // 2. Create PatientHistory
            $recordNumber = $this->generateRecordNumber();

            $history = PatientHistory::create([
                'record_number' => $recordNumber,
                'patient_id' => $patient->id,
                'doctor_id' => $doctorId,
                'doctor_name' => $doctorName,
                'visit_date' => $validated['visit_date'],
                'procedures' => $validated['procedures'],
                'tooth_numbers' => $validated['tooth_numbers'] ?? null,
                'diagnosis' => $validated['diagnosis'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'total_cost' => $totalCost,
                'paid_amount' => $paidAmount,
                'due_amount' => $dueAmount,
                'status' => $status,
                'created_by' => auth()->id(),
            ]);

            // 3. Log initial payment if paid > 0
            if ($paidAmount > 0) {
                PatientHistoryPayment::create([
                    'patient_history_id' => $history->id,
                    'patient_id' => $patient->id,
                    'payment_date' => $validated['visit_date'],
                    'amount' => $paidAmount,
                    'payment_method' => $validated['payment_method'] ?? 'cash',
                    'reference' => $validated['payment_reference'] ?? 'Opening Initial Payment',
                    'notes' => 'Previous payment record before app launch for '.$history->record_number,
                    'received_by' => auth()->id(),
                ]);
            }

            return $history;
        });

        return redirect()
            ->route('patient-histories.show', $patientHistory)
            ->with('success', "Historical patient record {$patientHistory->record_number} created successfully.");
    }

    public function show(PatientHistory $patientHistory)
    {
        if (auth()->user()->isDoctor() && $patientHistory->doctor_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this historical record.');
        }

        $patientHistory->load(['patient', 'doctor', 'createdBy', 'payments.receivedBy']);

        return view('patient-histories.show', [
            'history' => $patientHistory,
        ]);
    }

    public function edit(PatientHistory $patientHistory)
    {
        if (auth()->user()->isDoctor() && $patientHistory->doctor_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this historical record.');
        }

        $patientHistory->load(['patient', 'doctor']);
        $doctors = User::whereIn('role', [User::ROLE_DOCTOR, User::ROLE_ADMIN])->orderBy('name')->get();

        return view('patient-histories.edit', [
            'history' => $patientHistory,
            'doctors' => $doctors,
        ]);
    }

    public function update(Request $request, PatientHistory $patientHistory)
    {
        if (auth()->user()->isDoctor() && $patientHistory->doctor_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this historical record.');
        }

        $validated = $request->validate([
            'visit_date' => 'required|date',
            'doctor_id' => 'nullable|exists:users,id',
            'doctor_name' => 'nullable|string|max:255',
            'procedures' => 'required|string|max:2000',
            'tooth_numbers' => 'nullable|string|max:255',
            'diagnosis' => 'nullable|string|max:2000',
            'notes' => 'nullable|string|max:2000',
            'total_cost' => 'required|numeric|min:0',
        ]);

        $totalCost = (float) $validated['total_cost'];
        $paidAmount = (float) $patientHistory->paid_amount;

        if ($totalCost < $paidAmount) {
            throw ValidationException::withMessages([
                'total_cost' => 'Total cost cannot be less than already paid amount of PKR '.number_format($paidAmount, 2).'.',
            ]);
        }

        $dueAmount = max(0, $totalCost - $paidAmount);
        $status = $dueAmount == 0 ? PatientHistory::STATUS_PAID : ($paidAmount > 0 ? PatientHistory::STATUS_PARTIAL : PatientHistory::STATUS_PENDING);

        $doctorId = auth()->user()->isDoctor() ? auth()->id() : ($validated['doctor_id'] ?? $patientHistory->doctor_id);
        $doctorName = $doctorId ? User::find($doctorId)?->name : ($validated['doctor_name'] ?? $patientHistory->doctor_name);

        $patientHistory->update([
            'visit_date' => $validated['visit_date'],
            'doctor_id' => $doctorId,
            'doctor_name' => $doctorName,
            'procedures' => $validated['procedures'],
            'tooth_numbers' => $validated['tooth_numbers'] ?? null,
            'diagnosis' => $validated['diagnosis'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'total_cost' => $totalCost,
            'due_amount' => $dueAmount,
            'status' => $status,
        ]);

        return redirect()
            ->route('patient-histories.show', $patientHistory)
            ->with('success', 'Historical record updated successfully.');
    }

    public function destroy(PatientHistory $patientHistory)
    {
        if (auth()->user()->isDoctor() && $patientHistory->doctor_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this historical record.');
        }

        $patientId = $patientHistory->patient_id;
        $patientHistory->delete();

        // Check if patient has any remaining historical records; if not, update flag
        if (! PatientHistory::where('patient_id', $patientId)->exists()) {
            Patient::where('id', $patientId)->update(['is_historical' => false]);
        }

        return redirect()
            ->route('patient-histories.index')
            ->with('success', 'Historical record deleted successfully.');
    }

    /**
     * Financial Overview & Outstandings specifically for Historical / Pre-App Patients
     */
    /**
     * Historical outstandings are now unified into the main Outstanding Dues ledger (payments.outstanding).
     */
    public function outstanding(Request $request)
    {
        return redirect()->route('payments.outstanding');
    }

    /**
     * Receive payment against previous/historical due
     */
    public function recordPayment(Request $request, PatientHistory $patientHistory)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1|max:'.$patientHistory->due_amount,
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank,card,online,other',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $amount = (float) $validated['amount'];

        DB::transaction(function () use ($patientHistory, $validated, $amount) {
            $newPaid = $patientHistory->paid_amount + $amount;
            $newDue = max(0, $patientHistory->total_cost - $newPaid);
            $newStatus = $newDue == 0 ? PatientHistory::STATUS_PAID : PatientHistory::STATUS_PARTIAL;

            $patientHistory->update([
                'paid_amount' => $newPaid,
                'due_amount' => $newDue,
                'status' => $newStatus,
            ]);

            PatientHistoryPayment::create([
                'patient_history_id' => $patientHistory->id,
                'patient_id' => $patientHistory->patient_id,
                'payment_date' => $validated['payment_date'],
                'amount' => $amount,
                'payment_method' => $validated['payment_method'],
                'reference' => $validated['reference'] ?? null,
                'notes' => $validated['notes'] ?? 'Payment received against historical dues for '.$patientHistory->record_number,
                'received_by' => auth()->id(),
            ]);
        });

        $message = 'Payment of PKR '.number_format($amount, 2).' recorded against historical record '.$patientHistory->record_number.'. Remaining due: PKR '.number_format($patientHistory->fresh()->due_amount, 2).'.';

        if ($request->filled('redirect_url')) {
            return redirect($request->input('redirect_url'))->with('success', $message);
        }

        return back()->with('success', $message);
    }
}
