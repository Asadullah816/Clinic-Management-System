<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\MedicineUsage;
use App\Models\Patient;
use App\Models\Treatment;
use Illuminate\Http\Request;

class MedicineUsageController extends Controller
{
    public function index(Request $request)
    {
        $usages = MedicineUsage::with(['patient', 'treatment', 'medicine', 'usedBy'])
            ->when($request->filled('patient_id'), function ($query) use ($request) {
                $query->where('patient_id', $request->patient_id);
            })
            ->when($request->filled('medicine_id'), function ($query) use ($request) {
                $query->where('medicine_id', $request->medicine_id);
            })
            ->orderByDesc('usage_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('medicine-usages.index', [
            'usages'    => $usages,
            'patients'  => Patient::orderBy('first_name')->orderBy('last_name')->get(),
            'medicines' => Medicine::withTrashed()->orderBy('name')->get(), // include deleted: history shows them
        ]);
    }

    public function create(Request $request)
    {
        return view('medicine-usages.create', [
            'patients'   => Patient::where('status', Patient::STATUS_ACTIVE)
                                   ->orderBy('first_name')->orderBy('last_name')->get(),
            'medicines'  => Medicine::where('status', Medicine::STATUS_ACTIVE)->orderBy('name')->get(),
            'treatments' => Treatment::where('status', Treatment::STATUS_ACTIVE)->orderBy('name')->get(),
            'patientId'  => $request->query('patient_id'),   // preselect from patient profile
            'medicineId' => $request->query('medicine_id'),  // preselect from product page
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id'   => 'required|exists:patients,id',
            'treatment_id' => 'nullable|exists:treatments,id',
            'medicine_id'  => 'required|exists:medicines,id',
            'quantity'     => 'required|integer|min:1',
            'usage_date'   => 'required|date',
            'notes'        => 'nullable|string|max:2000',
        ]);

        $medicine = Medicine::findOrFail($validated['medicine_id']);
        $quantity = (int) $validated['quantity'];

        // GUARD 1: expired products must never be used on patients
        if ($medicine->isExpired()) {
            return back()
                ->withErrors(['medicine_id' =>
                    '"' . $medicine->name . '" expired on ' .
                    $medicine->expiry_date->format('d M Y') . ' and cannot be used on patients.'])
                ->withInput();
        }

        // GUARD 2: inactive products are out of circulation
        if ($medicine->status !== Medicine::STATUS_ACTIVE) {
            return back()
                ->withErrors(['medicine_id' =>
                    '"' . $medicine->name . '" is inactive and cannot be used.'])
                ->withInput();
        }

        // GUARD 3: stock can never go negative (same rule as the Stock module)
        if ($quantity > (int) $medicine->stock_quantity) {
            return back()
                ->withErrors(['quantity' =>
                    'Cannot use ' . $quantity . '; "' . $medicine->name . '" only has ' .
                    $medicine->stock_quantity . ' ' . ($medicine->unit ?? 'units') . ' in stock.'])
                ->withInput();
        }

        // The usage consumes stock — one decrement, then the record
        $medicine->stock_quantity -= $quantity;
        $medicine->save();

        MedicineUsage::create([
            'patient_id'   => $validated['patient_id'],
            'treatment_id' => $validated['treatment_id'] ?? null,
            'medicine_id'  => $medicine->id,
            'quantity'     => $quantity,
            'usage_date'   => $validated['usage_date'],
            'notes'        => $validated['notes'] ?? null,
            'used_by'      => auth()->id(),
        ]);

        $message = 'Recorded usage of ' . $quantity . ' × "' . $medicine->name .
            '". New stock: ' . $medicine->stock_quantity . '.';

        // Return to where the form was opened from
        if ($request->input('redirect') === 'patient') {
            return redirect()
                ->to(route('patients.show', $validated['patient_id']) . '#usage')
                ->with('success', $message);
        }

        if ($request->input('redirect') === 'medicine') {
            return redirect()
                ->to(route('medicines.show', $medicine) . '#usage-history')
                ->with('success', $message);
        }

        return redirect()->route('medicine-usages.index')->with('success', $message);
    }

    public function destroy(Request $request, MedicineUsage $medicine_usage)
    {
        $medicine = $medicine_usage->medicine; // withTrashed: works even if the product was soft-deleted

        // Reverse the usage: stock goes back up. No guard needed — adding stock
        // can never make the quantity negative.
        $medicine->stock_quantity += (int) $medicine_usage->quantity;
        $medicine->save();

        $patientId  = $medicine_usage->patient_id;
        $medicineId = $medicine_usage->medicine_id;

        $medicine_usage->delete();

        $message = 'Usage record deleted. Stock of "' . $medicine->name . '" restored to ' .
            $medicine->stock_quantity . '.';

        if ($request->query('redirect') === 'patient') {
            return redirect()
                ->to(route('patients.show', $patientId) . '#usage')
                ->with('success', $message);
        }

        if ($request->query('redirect') === 'medicine') {
            return redirect()
                ->to(route('medicines.show', $medicineId) . '#usage-history')
                ->with('success', $message);
        }

        return redirect()->route('medicine-usages.index')->with('success', $message);
    }
}
