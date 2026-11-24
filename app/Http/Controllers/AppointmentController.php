<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Treatment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    private function rules(?Appointment $appointment = null): array
    {
        $rules = [
            'patient_id' => 'required|exists:patients,id',
            'treatment_id' => 'nullable|exists:treatments,id',
            'treatment_ids' => 'nullable|array',
            'treatment_ids.0' => 'required_without:treatment_id|nullable|exists:treatments,id',
            'treatment_ids.*' => 'nullable|exists:treatments,id',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required|date_format:H:i',
            'status' => 'required|in:scheduled,completed,cancelled,no_show',
            'notes' => 'nullable|string|max:2000',
        ];

        if (! $appointment) {
            $rules['appointment_date'] .= '|after_or_equal:today';
        }

        return $rules;
    }

    public function bookedSlots(Request $request)
    {
        $date = $request->query('date', today()->toDateString());
        $excludeId = $request->query('exclude_id');

        $slots = Appointment::whereDate('appointment_date', $date)
            ->where('status', Appointment::STATUS_SCHEDULED)
            ->when($excludeId, function ($q) use ($excludeId) {
                $q->where('id', '!=', $excludeId);
            })
            ->with('patient:id,first_name,last_name,patient_number')
            ->orderBy('appointment_time')
            ->get(['id', 'patient_id', 'appointment_date', 'appointment_time'])
            ->map(function ($appt) {
                $timePart = substr($appt->appointment_time, 0, 5);

                return [
                    'id' => $appt->id,
                    'time' => $timePart,
                    'formatted_time' => Carbon::createFromFormat('H:i', $timePart)->format('h:i A'),
                    'patient_name' => $appt->patient?->full_name ?? 'Booked Patient',
                    'patient_number' => $appt->patient?->patient_number ?? '',
                ];
            });

        return response()->json($slots);
    }

    public function index(Request $request)
    {
        $appointments = Appointment::with(['patient', 'treatment', 'treatments'])
            // Search by patient name, patient number, or phone
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->whereHas('patient', function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('patient_number', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->filled('specific_date'), function ($query) use ($request) {
                $query->whereDate('appointment_date', $request->specific_date);
            })
            ->when($request->filled('date_filter') && ! $request->filled('specific_date'), function ($query) use ($request) {
                if ($request->date_filter === 'today') {
                    $query->whereDate('appointment_date', today());
                } elseif ($request->date_filter === 'tomorrow') {
                    $query->whereDate('appointment_date', today()->addDay());
                } elseif ($request->date_filter === 'upcoming') {
                    $query->whereDate('appointment_date', '>=', today());
                } elseif ($request->date_filter === 'past') {
                    $query->whereDate('appointment_date', '<', today());
                }
            })
            ->orderByDesc('appointment_date')
            ->orderBy('appointment_time')
            ->paginate(15)
            ->withQueryString();

        return view('appointments.index', [
            'appointments' => $appointments,
        ]);
    }

    public function create(Request $request)
    {
        return view('appointments.create', [
            'patients' => Patient::orderBy('first_name')->orderBy('last_name')->get(),
            'treatments' => Treatment::where('status', Treatment::STATUS_ACTIVE)->orderBy('name')->get(),
            'patientId' => $request->query('patient_id'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        $apptDateStr = Carbon::parse($validated['appointment_date'])->toDateString();

        // 1. Prohibit past date
        if ($apptDateStr < today()->toDateString()) {
            return back()
                ->withErrors(['appointment_date' => 'Appointments cannot be booked in the past. Please select today or a future date.'])
                ->withInput();
        }

        // 2. Prohibit past time if booked for today
        if ($apptDateStr === today()->toDateString()) {
            $currentTime = now()->format('H:i');
            $apptTime = substr($validated['appointment_time'], 0, 5);
            if ($apptTime < $currentTime) {
                return back()
                    ->withErrors(['appointment_time' => 'Cannot book an appointment for a past time today (current time is '.now()->format('h:i A').').'])
                    ->withInput();
            }
        }

        // 3. Prevent double-booking slot conflict on the same date and time (only active scheduled appointments)
        $apptTimeFormatted = substr($validated['appointment_time'], 0, 5);
        $conflict = Appointment::whereDate('appointment_date', $apptDateStr)
            ->where('appointment_time', 'like', $apptTimeFormatted.'%')
            ->where('status', Appointment::STATUS_SCHEDULED)
            ->with('patient')
            ->first();

        if ($conflict) {
            $patientName = $conflict->patient->full_name ?? 'another patient';
            $timeDisplay = Carbon::createFromFormat('H:i', $apptTimeFormatted)->format('h:i A');
            $dateDisplay = Carbon::parse($apptDateStr)->format('d M Y');

            return back()
                ->withErrors([
                    'appointment_time' => "Time slot {$timeDisplay} on {$dateDisplay} is already booked for {$patientName} (#{$conflict->patient->patient_number}). Please select a different time or date.",
                ])
                ->withInput();
        }

        // 4. Resolve multiple treatments: first is required, others are optional
        $treatmentIds = [];
        if ($request->has('treatment_ids')) {
            foreach ((array) $request->treatment_ids as $tid) {
                if (! empty($tid) && is_numeric($tid)) {
                    $treatmentIds[] = (int) $tid;
                }
            }
            $treatmentIds = array_values(array_unique($treatmentIds));
        } elseif (! empty($validated['treatment_id'])) {
            $treatmentIds = [(int) $validated['treatment_id']];
        }

        if (empty($treatmentIds)) {
            return back()
                ->withErrors(['treatment_ids.0' => 'The primary treatment is required. Please select at least one treatment.'])
                ->withInput();
        }

        $validated['treatment_id'] = $treatmentIds[0];
        $validated['created_by'] = auth()->id();

        $appointment = Appointment::create($validated);

        // Sync to pivot table
        $syncData = [];
        foreach ($treatmentIds as $idx => $tid) {
            $syncData[$tid] = ['is_primary' => ($idx === 0)];
        }
        $appointment->treatments()->sync($syncData);

        return redirect()
            ->route('appointments.index')
            ->with('success', 'Appointment booked successfully.');
    }

    public function show(Appointment $appointment)
    {
        $appointment->load(['patient', 'treatment', 'treatments', 'createdBy']);

        return view('appointments.show', [
            'appointment' => $appointment,
        ]);
    }

    public function edit(Appointment $appointment)
    {
        $appointment->load(['treatments', 'patient']);

        return view('appointments.edit', [
            'appointment' => $appointment,
            'patients' => Patient::orderBy('first_name')->orderBy('last_name')->get(),
            'treatments' => Treatment::where('status', Treatment::STATUS_ACTIVE)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Appointment $appointment)
    {
        $validated = $request->validate($this->rules($appointment));

        $apptDateStr = Carbon::parse($validated['appointment_date'])->toDateString();

        // 1. Prohibit past date/time for active scheduled appointments
        if ($validated['status'] === Appointment::STATUS_SCHEDULED) {
            if ($apptDateStr < today()->toDateString()) {
                return back()
                    ->withErrors(['appointment_date' => 'Scheduled appointments cannot be updated to past dates.'])
                    ->withInput();
            }
            if ($apptDateStr === today()->toDateString()) {
                $currentTime = now()->format('H:i');
                $apptTime = substr($validated['appointment_time'], 0, 5);
                if ($apptDateStr !== $appointment->appointment_date->toDateString() || $apptTime !== substr($appointment->appointment_time, 0, 5)) {
                    if ($apptTime < $currentTime) {
                        return back()
                            ->withErrors(['appointment_time' => 'Cannot reschedule for a past time today (current time is '.now()->format('h:i A').').'])
                            ->withInput();
                    }
                }
            }
        }

        // 2. Prevent double booking slot conflict (only active scheduled appointments)
        $apptTimeFormatted = substr($validated['appointment_time'], 0, 5);
        $conflict = Appointment::whereDate('appointment_date', $apptDateStr)
            ->where('appointment_time', 'like', $apptTimeFormatted.'%')
            ->where('status', Appointment::STATUS_SCHEDULED)
            ->where('id', '!=', $appointment->id)
            ->with('patient')
            ->first();

        if ($conflict) {
            $patientName = $conflict->patient->full_name ?? 'another patient';
            $timeDisplay = Carbon::createFromFormat('H:i', $apptTimeFormatted)->format('h:i A');
            $dateDisplay = Carbon::parse($apptDateStr)->format('d M Y');

            return back()
                ->withErrors([
                    'appointment_time' => "Time slot {$timeDisplay} on {$dateDisplay} is already booked for {$patientName} (#{$conflict->patient->patient_number}). Please select a different time or date.",
                ])
                ->withInput();
        }

        // 3. Resolve multiple treatments
        $treatmentIds = [];
        if ($request->has('treatment_ids')) {
            foreach ((array) $request->treatment_ids as $tid) {
                if (! empty($tid) && is_numeric($tid)) {
                    $treatmentIds[] = (int) $tid;
                }
            }
            $treatmentIds = array_values(array_unique($treatmentIds));
        } elseif (! empty($validated['treatment_id'])) {
            $treatmentIds = [(int) $validated['treatment_id']];
        }

        if (empty($treatmentIds)) {
            return back()
                ->withErrors(['treatment_ids.0' => 'The primary treatment is required. Please select at least one treatment.'])
                ->withInput();
        }

        $validated['treatment_id'] = $treatmentIds[0];
        $appointment->update($validated);

        $syncData = [];
        foreach ($treatmentIds as $idx => $tid) {
            $syncData[$tid] = ['is_primary' => ($idx === 0)];
        }
        $appointment->treatments()->sync($syncData);

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
                ->to(route('patients.show', $appointment->patient_id).'#appointments')
                ->with('success', 'Appointment deleted successfully.');
        }

        return redirect()
            ->route('appointments.index')
            ->with('success', 'Appointment deleted successfully.');
    }
}
