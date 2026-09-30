<?php

namespace App\Http\Controllers;

use App\Models\LaserTreatment;
use Illuminate\Http\Request;

class LaserTreatmentController extends Controller
{
    private function rules(?int $id = null): array
    {
        return [
            'name' => 'required|string|max:150',
            'code' => 'nullable|string|max:50',
            'body_area' => 'nullable|string|max:100',
            'price' => 'required|numeric|min:0',
            'duration' => 'nullable|integer|min:5|max:480',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
        ];
    }

    public function index(Request $request)
    {
        $treatments = LaserTreatment::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('body_area', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->withCount('sessions')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('laser.treatments.index', [
            'treatments' => $treatments,
        ]);
    }

    public function create()
    {
        return view('laser.treatments.create', [
            'bodyAreas' => LaserTreatment::commonBodyAreas(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        $treatment = LaserTreatment::create($validated);

        return redirect()
            ->route('laser.treatments.index')
            ->with('success', "Laser Treatment '{$treatment->name}' added to catalog.");
    }

    public function edit(LaserTreatment $treatment)
    {
        return view('laser.treatments.edit', [
            'treatment' => $treatment,
            'bodyAreas' => LaserTreatment::commonBodyAreas(),
        ]);
    }

    public function update(Request $request, LaserTreatment $treatment)
    {
        $validated = $request->validate($this->rules($treatment->id));

        $treatment->update($validated);

        return redirect()
            ->route('laser.treatments.index')
            ->with('success', "Laser Treatment '{$treatment->name}' updated successfully.");
    }

    public function destroy(LaserTreatment $treatment)
    {
        $treatment->delete();

        return redirect()
            ->route('laser.treatments.index')
            ->with('success', "Laser Treatment '{$treatment->name}' removed from active catalog.");
    }
}
