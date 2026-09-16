<?php

namespace App\Http\Controllers;

use App\Models\MedicalHistory;
use App\Models\Patient;
use Illuminate\Http\Request;

class MedicalHistoryController extends Controller
{
    /**
     * Validation rules shared by store() and update().
     */
    private function rules(): array
    {
        return [
            'visit_date'          => 'required|date',
            'chief_complaint'     => 'required|string|max:1000',
            'diagnosis'           => 'nullable|string|max:2000',
            'previous_treatment'  => 'nullable|string|max:2000',
            'allergies'           => 'nullable|string|max:1000',
            'medical_conditions'  => 'nullable|string|max:2000',
            'current_medications' => 'nullable|string|max:2000',
            'notes'               => 'nullable|string|max:2000',
        ];
    }

    public function create(Patient $patient)
    {
        return view('medical-histories.create', [
            'patient' => $patient,
        ]);
    }

    public function store(Request $request, Patient $patient)
    {
        $validated = $request->validate($this->rules());

        // create() through the relationship sets patient_id automatically
        $patient->medicalHistories()->create($validated + [
            'created_by' => auth()->id(),
        ]);

        // Redirect back to the profile, straight onto the Medical History tab
        return redirect()
            ->to(route('patients.show', $patient) . '#medical-history')
            ->with('success', 'Medical history record added successfully.');
    }

    public function edit(MedicalHistory $medicalHistory)
    {
        return view('medical-histories.edit', [
            'medicalHistory' => $medicalHistory,
            'patient'        => $medicalHistory->patient,
        ]);
    }

    public function update(Request $request, MedicalHistory $medicalHistory)
    {
        $validated = $request->validate($this->rules());

        $medicalHistory->update($validated);

        return redirect()
            ->to(route('patients.show', $medicalHistory->patient_id) . '#medical-history')
            ->with('success', 'Medical history record updated successfully.');
    }

    public function destroy(MedicalHistory $medicalHistory)
    {
        $patientId = $medicalHistory->patient_id;

        $medicalHistory->delete();

        return redirect()
            ->to(route('patients.show', $patientId) . '#medical-history')
            ->with('success', 'Medical history record deleted successfully.');
    }
}
