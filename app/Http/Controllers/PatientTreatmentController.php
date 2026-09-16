<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\PatientTreatment;
use App\Models\Treatment;
use Illuminate\Http\Request;

class PatientTreatmentController extends Controller
{
    private function rules(): array
    {
        return [
            'patient_id'     => 'required|exists:patients,id',
            'treatment_id'   => 'required|exists:treatments,id',
            'treatment_date' => 'required|date',
            'price'          => 'required|numeric|min:0',
            // lte:price → discount may not exceed the price (total stays >= 0)
            'discount'       => 'nullable|numeric|min:0|lte:price',
            'notes'          => 'nullable|string|max:2000',
        ];
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
            'patients'    => Patient::orderBy('first_name')->orderBy('last_name')->get(),
            'treatments'  => Treatment::where('status', Treatment::STATUS_ACTIVE)->orderBy('name')->get(),
            'patientId'   => $request->query('patient_id'),
            'treatmentId' => $request->query('treatment_id'),
            'price'       => $treatment?->price, // snapshot price prefill
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        $discount = $validated['discount'] ?? 0;

        PatientTreatment::create([
            'patient_id'     => $validated['patient_id'],
            'treatment_id'   => $validated['treatment_id'],
            'treatment_date' => $validated['treatment_date'],
            'price'          => $validated['price'],
            'discount'       => $discount,
            'total_amount'   => $validated['price'] - $discount, // THE calculation
            'notes'          => $validated['notes'] ?? null,
            'created_by'     => auth()->id(),
        ]);

        return redirect()
            ->route('patient-treatments.index')
            ->with('success', 'Treatment recorded successfully.');
    }

    public function edit(PatientTreatment $patientTreatment)
    {
        return view('patient-treatments.edit', [
            'patientTreatment' => $patientTreatment,
            'patients'         => Patient::orderBy('first_name')->orderBy('last_name')->get(),
            'treatments'       => Treatment::where('status', Treatment::STATUS_ACTIVE)->orderBy('name')->get(),
            'patientId'        => null,
            'treatmentId'      => null,
            'price'            => $patientTreatment->price,
        ]);
    }

    public function update(Request $request, PatientTreatment $patientTreatment)
    {
        $validated = $request->validate($this->rules());

        $discount = $validated['discount'] ?? 0;

        $patientTreatment->update([
            'patient_id'     => $validated['patient_id'],
            'treatment_id'   => $validated['treatment_id'],
            'treatment_date' => $validated['treatment_date'],
            'price'          => $validated['price'],
            'discount'       => $discount,
            'total_amount'   => $validated['price'] - $discount,
            'notes'          => $validated['notes'] ?? null,
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
                ->to(route('patients.show', $patientTreatment->patient_id) . '#treatments')
                ->with('success', 'Treatment record deleted successfully.');
        }

        return redirect()
            ->route('patient-treatments.index')
            ->with('success', 'Treatment record deleted successfully.');
    }
}
