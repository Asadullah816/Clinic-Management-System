<?php

namespace App\Http\Controllers;

use App\Models\ImplantBuyer;
use App\Models\ImplantSale;
use Illuminate\Http\Request;

class ImplantBuyerController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $query = ImplantBuyer::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('doctor_name', 'like', "%{$search}%")
                    ->orWhere('clinic_hospital_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $buyers = $query->with('sales')->orderBy('is_internal', 'desc')->orderBy('doctor_name')->paginate(15)->withQueryString();

        $stats = [
            'total_doctors' => ImplantBuyer::where('is_internal', false)->count(),
            'total_sales_value' => (float) ImplantSale::sum('total_amount'),
            'total_paid' => (float) ImplantSale::sum('paid_amount'),
            'total_due' => (float) ImplantSale::sum('due_amount'),
        ];

        return view('implant-buyers.index', compact('buyers', 'stats', 'search'));
    }

    public function create()
    {
        return view('implant-buyers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'doctor_name' => 'required|string|max:255',
            'clinic_hospital_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'is_internal' => 'nullable|boolean',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $validated['is_internal'] = $request->boolean('is_internal');

        ImplantBuyer::create($validated);

        return redirect()->route('implant-buyers.index')->with('success', 'Doctor / Client profile registered successfully.');
    }

    public function show(ImplantBuyer $implantBuyer)
    {
        $implantBuyer->load(['sales.items.implant', 'payments.sale']);

        return view('implant-buyers.show', compact('implantBuyer'));
    }

    public function edit(ImplantBuyer $implantBuyer)
    {
        return view('implant-buyers.edit', compact('implantBuyer'));
    }

    public function update(Request $request, ImplantBuyer $implantBuyer)
    {
        $validated = $request->validate([
            'doctor_name' => 'required|string|max:255',
            'clinic_hospital_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'is_internal' => 'nullable|boolean',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $validated['is_internal'] = $request->boolean('is_internal');

        $implantBuyer->update($validated);

        return redirect()->route('implant-buyers.show', $implantBuyer)->with('success', 'Doctor / Client profile updated successfully.');
    }

    public function destroy(ImplantBuyer $implantBuyer)
    {
        if ($implantBuyer->sales()->exists()) {
            return back()->with('error', 'Cannot delete doctor with recorded sales history. You can deactivate the account instead.');
        }

        $implantBuyer->delete();

        return redirect()->route('implant-buyers.index')->with('success', 'Doctor profile deleted successfully.');
    }
}
