<?php

namespace App\Http\Controllers;

use App\Models\Treatment;
use Illuminate\Http\Request;

class TreatmentController extends Controller
{
    private function rules(): array
    {
        return [
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'price'       => 'required|numeric|min:0',
            'duration'    => 'nullable|integer|min:1|max:1440', // minutes, max 24h
            'status'      => 'required|in:active,inactive',
        ];
    }

    public function index(Request $request)
    {
        $treatments = Treatment::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('treatments.index', [
            'treatments' => $treatments,
        ]);
    }

    public function create()
    {
        return view('treatments.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        Treatment::create($validated);

        return redirect()
            ->route('treatments.index')
            ->with('success', 'Treatment created successfully.');
    }

    public function edit(Treatment $treatment)
    {
        return view('treatments.edit', [
            'treatment' => $treatment,
        ]);
    }

    public function update(Request $request, Treatment $treatment)
    {
        $validated = $request->validate($this->rules());

        $treatment->update($validated);

        return redirect()
            ->route('treatments.index')
            ->with('success', 'Treatment updated successfully.');
    }

    public function destroy(Treatment $treatment)
    {
        $treatment->delete(); // soft delete — historical records keep the name via withTrashed()

        return redirect()
            ->route('treatments.index')
            ->with('success', 'Treatment deleted successfully.');
    }
}
