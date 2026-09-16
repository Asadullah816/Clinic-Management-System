<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use Illuminate\Http\Request;

class ExpenseCategoryController extends Controller
{
    private function rules(ExpenseCategory $category = null): array
    {
        $uniqueRule = $category
            ? 'unique:expense_categories,name,' . $category->id
            : 'unique:expense_categories,name';

        return [
            'name'        => 'required|string|max:255|' . $uniqueRule,
            'description' => 'nullable|string|max:1000',
            'status'      => 'required|in:active,inactive',
        ];
    }

    public function index(Request $request)
    {
        $categories = ExpenseCategory::query()
            ->withCount('expenses') // gives $category->expenses_count
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search . '%');
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('expense-categories.index', [
            'categories' => $categories,
        ]);
    }

    public function create()
    {
        return view('expense-categories.create');
    }

    public function store(Request $request)
    {
        $request->validate($this->rules());

        ExpenseCategory::create($request->all());

        return redirect()
            ->route('expense-categories.index')
            ->with('success', 'Expense category created successfully.');
    }

    public function edit(ExpenseCategory $expense_category)
    {
        return view('expense-categories.edit', [
            'category' => $expense_category,
        ]);
    }

    public function update(Request $request, ExpenseCategory $expense_category)
    {
        $request->validate($this->rules($expense_category));

        $expense_category->update($request->all());

        return redirect()
            ->route('expense-categories.index')
            ->with('success', 'Expense category updated successfully.');
    }

    public function destroy(ExpenseCategory $expense_category)
    {
        // Guard: never delete a category that has expenses recorded against it
        if ($expense_category->expenses()->exists()) {
            return redirect()
                ->route('expense-categories.index')
                ->with('error', 'This category has expenses recorded and cannot be deleted. Set it to inactive instead.');
        }

        $expense_category->delete();

        return redirect()
            ->route('expense-categories.index')
            ->with('success', 'Expense category deleted successfully.');
    }
}
