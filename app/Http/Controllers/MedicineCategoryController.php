<?php

namespace App\Http\Controllers;

use App\Models\MedicineCategory;
use Illuminate\Http\Request;

class MedicineCategoryController extends Controller
{
    private function rules(MedicineCategory $category = null): array
    {
        // 'ignore self' on update so a category can keep its own name
        $uniqueRule = $category
            ? 'unique:medicine_categories,name,' . $category->id
            : 'unique:medicine_categories,name';

        return [
            'name'        => 'required|string|max:255|' . $uniqueRule,
            'description' => 'nullable|string|max:1000',
            'status'      => 'required|in:active,inactive',
        ];
    }

    public function index(Request $request)
    {
        $categories = MedicineCategory::query()
            ->withCount('medicines') // gives $category->medicines_count
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search . '%');
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('medicine-categories.index', [
            'categories' => $categories,
        ]);
    }

    public function create()
    {
        return view('medicine-categories.create');
    }

    public function store(Request $request)
    {
        $request->validate($this->rules());

        MedicineCategory::create($request->all());

        return redirect()
            ->route('medicine-categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function edit(MedicineCategory $medicine_category)
    {
        return view('medicine-categories.edit', [
            'category' => $medicine_category,
        ]);
    }

    public function update(Request $request, MedicineCategory $medicine_category)
    {
        $request->validate($this->rules($medicine_category));

        $medicine_category->update($request->all());

        return redirect()
            ->route('medicine-categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(MedicineCategory $medicine_category)
    {
        // Guard: never delete a category that still has products
        if ($medicine_category->medicines()->exists()) {
            return redirect()
                ->route('medicine-categories.index')
                ->with('error', 'This category has products and cannot be deleted. Set it to inactive instead.');
        }

        $medicine_category->delete();

        return redirect()
            ->route('medicine-categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}
