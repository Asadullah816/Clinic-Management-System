<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Treatment;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    private function rules(): array
    {
        return [
            'patient_id'       => 'required|exists:patients,id',
            'treatment_id'     => 'nullable|exists:treatments,id',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required|date_format:H:i',
            'status'           => 'required|in:scheduled,completed,cancelled,no_show',
            'notes'            => 'nullable|string|max:2000',
        ];
    }

    public function index(Request $request)
    {
        $appointments = Appointment::with(['patient', 'treatment'])
            // Search by patient name or patient number
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->whereHas('patient', function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('patient_number', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->filled('date_filter'), function ($query) use ($request) {
                if ($request->date_filter === 'today') {
                    $query->whereDate('appointment_date', today());
                } elseif ($request->date_filter === 'upcoming') {
                    $query->whereDate('appointment_date', '>=', today());
                } elseif ($request->date_filter === 'past') {
                    $query->whereDate('appointment_date', '<', today());
                }
            })
            ->orderByDesc('appointment_date')
            ->orderBy('appointment_time')
            ->paginate(10)
            ->withQueryString();

        return view('appointments.index', [
            'appointments' => $appointments,
        ]);
    }

    public function create(Request $request)
    {
        return view('appointments.create', [
            'patients'   => Patient::orderBy('first_name')->orderBy('last_name')->get(),
            'treatments' => Treatment::where('status', Treatment::STATUS_ACTIVE)->orderBy('name')->get(),
            // From the patient profile we link here with ?patient_id=X to preselect the patient
            'patientId'  => $request->query('patient_id'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        $validated['created_by'] = auth()->id();

        Appointment::create($validated);

        return redirect()
            ->route('appointments.index')
            ->with('success', 'Appointment created successfully.');
    }

    public function show(Appointment $appointment)
    {
        $appointment->load(['patient', 'treatment', 'createdBy']);

        return view('appointments.show', [
            'appointment' => $appointment,
        ]);
    }

    public function edit(Appointment $appointment)
    {
        return view('appointments.edit', [
            'appointment' => $appointment,
            'patients'    => Patient::orderBy('first_name')->orderBy('last_name')->get(),
            'treatments'  => Treatment::where('status', Treatment::STATUS_ACTIVE)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Appointment $appointment)
    {
        $validated = $request->validate($this->rules());

        $appointment->update($validated);

        return redirect()
            ->route('appointments.index')
            ->with('success', 'Appointment updated successfully.');
    }

    public function destroy(Request $request, Appointment $appointment)
    {
        $appointment->delete();

        // When deleted from the patient profile tab, go back to that profile
        if ($request->query('redirect') === 'patient') {
            return redirect()
                ->to(route('patients.show', $appointment->patient_id) . '#appointments')
                ->with('success', 'Appointment deleted successfully.');
        }

        return redirect()
            ->route('appointments.index')
            ->with('success', 'Appointment deleted successfully.');
    }
}
