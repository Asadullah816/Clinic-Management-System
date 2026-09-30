<?php

namespace App\Http\Controllers;

use App\Models\LaserPatient;
use Illuminate\Http\Request;

class LaserPatientController extends Controller
{
    private function rules(?int $id = null): array
    {
        return [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'gender' => 'required|in:male,female,other',
            'date_of_birth' => 'nullable|date',
            'phone' => 'required|string|max:30',
            'email' => 'nullable|email|max:150',
            'address' => 'nullable|string|max:500',
            'emergency_contact' => 'nullable|string|max:100',
            'skin_type' => 'nullable|string|max:50',
            'medical_notes' => 'nullable|string|max:2000',
            'referred_by' => 'nullable|string|max:100',
            'status' => 'required|in:active,inactive',
        ];
    }

    private function generatePatientNumber(): string
    {
        $nextId = (LaserPatient::withTrashed()->max('id') ?? 0) + 1;
        $num = 'LP-'.str_pad($nextId, 4, '0', STR_PAD_LEFT);

        while (LaserPatient::withTrashed()->where('patient_number', $num)->exists()) {
            $nextId++;
            $num = 'LP-'.str_pad($nextId, 4, '0', STR_PAD_LEFT);
        }

        return $num;
    }

    public function index(Request $request)
    {
        $patients = LaserPatient::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('patient_number', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->filled('skin_type'), function ($query) use ($request) {
                $query->where('skin_type', $request->skin_type);
            })
            ->withCount('sessions')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->paginate(15)
            ->withQueryString();

        return view('laser.patients.index', [
            'patients' => $patients,
        ]);
    }

    public function create()
    {
        return view('laser.patients.create', [
            'fitzpatrickTypes' => LaserPatient::fitzpatrickTypes(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        $patientNumber = $this->generatePatientNumber();

        $patient = LaserPatient::create(array_merge($validated, [
            'patient_number' => $patientNumber,
            'created_by' => auth()->id(),
        ]));

        return redirect()
            ->route('laser.patients.show', $patient)
            ->with('success', "Laser Patient {$patient->full_name} ({$patient->patient_number}) registered successfully.");
    }

    public function show(LaserPatient $patient)
    {
        $patient->load([
            'sessions.treatment',
            'sessions.performedBy',
            'createdBy',
        ]);

        return view('laser.patients.show', [
            'patient' => $patient,
        ]);
    }

    public function edit(LaserPatient $patient)
    {
        return view('laser.patients.edit', [
            'patient' => $patient,
            'fitzpatrickTypes' => LaserPatient::fitzpatrickTypes(),
        ]);
    }

    public function update(Request $request, LaserPatient $patient)
    {
        $validated = $request->validate($this->rules($patient->id));

        $patient->update($validated);

        return redirect()
            ->route('laser.patients.show', $patient)
            ->with('success', "Laser Patient {$patient->full_name} updated successfully.");
    }

    public function destroy(LaserPatient $patient)
    {
        if ($patient->sessions()->exists()) {
            return redirect()
                ->route('laser.patients.show', $patient)
                ->with('error', 'Cannot delete patient with recorded laser sessions.');
        }

        $patient->delete();

        return redirect()
            ->route('laser.patients.index')
            ->with('success', 'Laser Patient deleted successfully.');
    }
}
