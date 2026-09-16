<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $patients = Patient::query()
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

        return view('patients.index', [
            'patients' => $patients,
            'periods'  => $this->periods(),
        ]);
    }

    public function create()
    {
        return view('patients.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name'        => 'required|string|max:255',
            'last_name'         => 'required|string|max:255',
            'gender'            => 'required|in:male,female,other',
            'date_of_birth'     => 'nullable|date|before:today',
            'phone'             => 'required|string|max:30',
            'email'             => 'nullable|email|max:255',
            'address'           => 'nullable|string|max:1000',
            'emergency_contact' => 'nullable|string|max:30',
            'occupation'        => 'nullable|string|max:255',
            'allergies'         => 'nullable|string|max:1000',
            'skin_type'         => 'nullable|string|max:255',
            'medical_notes'     => 'nullable|string|max:2000',
            'referred_by'       => 'nullable|string|max:255',
            'status'            => 'required|in:active,inactive',
        ]);

        $patient = Patient::create($validated);

        // Generate the patient number from the auto-increment ID: P-0001, P-0002, ...
        $patient->update([
            'patient_number' => 'P-' . str_pad($patient->id, 4, '0', STR_PAD_LEFT),
        ]);

        return redirect()
            ->route('patients.show', $patient)
            ->with('success', 'Patient created successfully. Patient number: ' . $patient->patient_number);
    }

    public function show(Patient $patient)
    {
        $patient->load([
            'medicalHistories' => function ($query) {
                $query->with('createdBy')->orderByDesc('visit_date');
            },
            'appointments' => function ($query) {
                $query->with('treatment')
                      ->orderByDesc('appointment_date')
                      ->orderByDesc('appointment_time');
            },
            'patientTreatments' => function ($query) {
                $query->with('treatment')->orderByDesc('treatment_date');
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

        // Financial summary — all derived from stored columns
        return view('patients.show', [
            'patient'            => $patient,
            'totalTreatmentCost' => $patient->patientTreatments->sum('total_amount'),
            'totalInvoiced'      => $patient->invoices->sum('total_amount'),
            'totalPaid'          => $patient->invoices->sum('paid_amount'),
            'totalOutstanding'   => $patient->invoices->sum('due_amount'),
        ]);
    }

    public function edit(Patient $patient)
    {
        return view('patients.edit', [
            'patient' => $patient,
        ]);
    }

    public function update(Request $request, Patient $patient)
    {
        $validated = $request->validate([
            'first_name'        => 'required|string|max:255',
            'last_name'         => 'required|string|max:255',
            'gender'            => 'required|in:male,female,other',
            'date_of_birth'     => 'nullable|date|before:today',
            'phone'             => 'required|string|max:30',
            'email'             => 'nullable|email|max:255',
            'address'           => 'nullable|string|max:1000',
            'emergency_contact' => 'nullable|string|max:30',
            'occupation'        => 'nullable|string|max:255',
            'allergies'         => 'nullable|string|max:1000',
            'skin_type'         => 'nullable|string|max:255',
            'medical_notes'     => 'nullable|string|max:2000',
            'referred_by'       => 'nullable|string|max:255',
            'status'            => 'required|in:active,inactive',
        ]);

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
