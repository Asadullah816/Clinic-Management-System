<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $patients = Patient::with('assignedDoctor')
            // Doctor role scoping: only see their assigned or treated patients
            ->when($user && $user->isDoctor(), function ($query) use ($user) {
                $query->forDoctor($user->id);
            })
            // Admin/receptionist can filter by assigned doctor
            ->when($user && ! $user->isDoctor() && $request->filled('doctor_id'), function ($query) use ($request) {
                $query->where('assigned_doctor_id', $request->doctor_id);
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('patient_number', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->filled('period') && $request->period !== 'all', function ($query) use ($request) {
                [$start, $end] = $this->periodRange($request->period);
                $query->whereBetween('created_at', [$start, $end]);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $doctors = User::whereIn('role', [User::ROLE_DOCTOR, User::ROLE_ADMIN])->orderBy('name')->get();

        return view('patients.index', [
            'patients' => $patients,
            'periods' => $this->periods(),
            'doctors' => $doctors,
        ]);
    }

    public function create()
    {
        return view('patients.create', [
            'doctors' => User::whereIn('role', [User::ROLE_DOCTOR, User::ROLE_ADMIN])->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'assigned_doctor_id' => 'nullable|exists:users,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'gender' => 'required|in:male,female,other',
            'date_of_birth' => 'nullable|date|before:today',
            'phone' => 'required|string|max:30',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:1000',
            'emergency_contact' => 'nullable|string|max:30',
            'occupation' => 'nullable|string|max:255',
            'allergies' => 'nullable|string|max:1000',
            'skin_type' => 'nullable|string|max:255',
            'medical_notes' => 'nullable|string|max:2000',
            'referred_by' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        if (auth()->user()->isDoctor()) {
            $validated['assigned_doctor_id'] = auth()->id();
        }

        $patient = Patient::create($validated);

        // Generate the patient number from the auto-increment ID: P-0001, P-0002, ...
        $patient->update([
            'patient_number' => 'P-'.str_pad($patient->id, 4, '0', STR_PAD_LEFT),
        ]);

        return redirect()
            ->route('patients.show', $patient)
            ->with('success', 'Patient created successfully. Patient number: '.$patient->patient_number);
    }

    public function show(Patient $patient)
    {
        if (auth()->user()->isDoctor()) {
            $doctorId = auth()->id();
            $isAssigned = $patient->assigned_doctor_id === $doctorId;
            $hasAppointment = $patient->appointments()->where('doctor_id', $doctorId)->exists();
            $hasTreatment = $patient->patientTreatments()->where('doctor_id', $doctorId)->exists();
            $hasHistory = $patient->patientHistories()->where('doctor_id', $doctorId)->exists();

            if (! $isAssigned && ! $hasAppointment && ! $hasTreatment && ! $hasHistory) {
                abort(403, 'Unauthorized access to this patient record.');
            }
        }

        $patient->load([
            'assignedDoctor',
            'medicalHistories' => function ($query) {
                $query->with('createdBy')->orderByDesc('visit_date');
            },
            'appointments' => function ($query) {
                $query->with('treatment')
                    ->orderByDesc('appointment_date')
                    ->orderByDesc('appointment_time');
            },
            'patientTreatments' => function ($query) {
                $query->with(['treatment', 'treatmentMedicines.medicine'])->orderByDesc('treatment_date');
            },
            'patientHistories' => function ($query) {
                if (auth()->user()->isDoctor()) {
                    $query->forDoctor(auth()->id());
                }
                $query->with(['doctor', 'createdBy', 'payments.receivedBy'])->orderByDesc('visit_date')->orderByDesc('id');
            },
            'invoices' => function ($query) {
                $query->orderByDesc('invoice_date')->orderByDesc('id');
            },
            'payments' => function ($query) {
                $query->with(['invoice', 'receivedBy'])
                    ->orderByDesc('payment_date')
                    ->orderByDesc('id');
            },
            'medicineUsages' => function ($query) {
                $query->with(['treatment', 'medicine'])
                    ->orderByDesc('usage_date')
                    ->orderByDesc('id');
            },
        ]);

        $historicalTotal = (float) $patient->patientHistories->sum('total_cost');
        $historicalPaid = (float) $patient->patientHistories->sum('paid_amount');
        $historicalDue = (float) $patient->patientHistories->sum('due_amount');
        $totalInvoiced = (float) $patient->invoices->sum('total_amount');
        $totalPaid = (float) $patient->invoices->sum('paid_amount');
        $totalOutstanding = (float) $patient->invoices->sum('due_amount');
        $combinedDue = $historicalDue + $totalOutstanding;

        // Financial summary — all derived from stored columns
        return view('patients.show', [
            'patient' => $patient,
            'totalTreatmentCost' => (float) $patient->patientTreatments->sum('total_amount'),
            'totalInvoiced' => $totalInvoiced,
            'totalPaid' => $totalPaid,
            'totalOutstanding' => $totalOutstanding,
            'historicalTotal' => $historicalTotal,
            'historicalPaid' => $historicalPaid,
            'historicalDue' => $historicalDue,
            'combinedDue' => $combinedDue,
        ]);
    }

    public function edit(Patient $patient)
    {
        if (auth()->user()->isDoctor()) {
            $doctorId = auth()->id();
            $isAssigned = $patient->assigned_doctor_id === $doctorId;
            $hasAppointment = $patient->appointments()->where('doctor_id', $doctorId)->exists();
            $hasTreatment = $patient->patientTreatments()->where('doctor_id', $doctorId)->exists();
            $hasHistory = $patient->patientHistories()->where('doctor_id', $doctorId)->exists();

            if (! $isAssigned && ! $hasAppointment && ! $hasTreatment && ! $hasHistory) {
                abort(403, 'Unauthorized access to this patient record.');
            }
        }

        return view('patients.edit', [
            'patient' => $patient,
            'doctors' => User::whereIn('role', [User::ROLE_DOCTOR, User::ROLE_ADMIN])->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Patient $patient)
    {
        if (auth()->user()->isDoctor()) {
            $doctorId = auth()->id();
            $isAssigned = $patient->assigned_doctor_id === $doctorId;
            $hasAppointment = $patient->appointments()->where('doctor_id', $doctorId)->exists();
            $hasTreatment = $patient->patientTreatments()->where('doctor_id', $doctorId)->exists();

            if (! $isAssigned && ! $hasAppointment && ! $hasTreatment) {
                abort(403, 'Unauthorized access to this patient record.');
            }
        }

        $validated = $request->validate([
            'assigned_doctor_id' => 'nullable|exists:users,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'gender' => 'required|in:male,female,other',
            'date_of_birth' => 'nullable|date|before:today',
            'phone' => 'required|string|max:30',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:1000',
            'emergency_contact' => 'nullable|string|max:30',
            'occupation' => 'nullable|string|max:255',
            'allergies' => 'nullable|string|max:1000',
            'skin_type' => 'nullable|string|max:255',
            'medical_notes' => 'nullable|string|max:2000',
            'referred_by' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        if (auth()->user()->isDoctor() && empty($validated['assigned_doctor_id'])) {
            $validated['assigned_doctor_id'] = $patient->assigned_doctor_id ?: auth()->id();
        }

        $patient->update($validated);

        return redirect()
            ->route('patients.show', $patient)
            ->with('success', 'Patient updated successfully.');
    }

    public function destroy(Patient $patient)
    {
        $patient->delete(); // soft delete — history is preserved in the database

        return redirect()
            ->route('patients.index')
            ->with('success', 'Patient deleted successfully.');
    }
}
