<?php

namespace App\Http\Controllers;

use App\Models\LaserExpenseCategory;
use Illuminate\Http\Request;

class LaserExpenseCategoryController extends Controller
{
    private function rules(?int $id = null): array
    {
        return [
            'name' => 'required|string|max:100|unique:laser_expense_categories,name,'.$id,
            'description' => 'nullable|string|max:500',
        ];
    }

    public function index()
    {
        $categories = LaserExpenseCategory::withCount('expenses')
            ->orderBy('name')
            ->paginate(15);

        return view('laser.expense-categories.index', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        LaserExpenseCategory::create($validated);

        return redirect()
            ->route('laser.expense-categories.index')
            ->with('success', 'Laser expense category created successfully.');
    }

    public function update(Request $request, LaserExpenseCategory $laserExpenseCategory)
    {
        $validated = $request->validate($this->rules($laserExpenseCategory->id));

        $laserExpenseCategory->update($validated);

        return redirect()
            ->route('laser.expense-categories.index')
            ->with('success', 'Laser expense category updated successfully.');
    }

    public function destroy(LaserExpenseCategory $laserExpenseCategory)
    {
        if ($laserExpenseCategory->expenses()->exists()) {
            return redirect()
                ->route('laser.expense-categories.index')
                ->with('error', 'Cannot delete category that has recorded expenses.');
        }

        $laserExpenseCategory->delete();

        return redirect()
            ->route('laser.expense-categories.index')
            ->with('success', 'Laser expense category deleted successfully.');
    }
}
