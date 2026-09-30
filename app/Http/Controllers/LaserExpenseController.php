<?php

namespace App\Http\Controllers;

use App\Models\LaserExpense;
use App\Models\LaserExpenseCategory;
use Illuminate\Http\Request;

class LaserExpenseController extends Controller
{
    private function rules(): array
    {
        return [
            'laser_expense_category_id' => 'required|exists:laser_expense_categories,id',
            'title' => 'required|string|max:150',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank,card,online,other',
            'reference' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000',
        ];
    }

    public function index(Request $request)
    {
        $expenses = LaserExpense::with(['expenseCategory', 'createdBy'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('reference', 'like', "%{$search}%");
            })
            ->when($request->filled('category_id'), function ($query) use ($request) {
                $query->where('laser_expense_category_id', $request->category_id);
            })
            ->when($request->filled('from'), function ($query) use ($request) {
                $query->whereDate('expense_date', '>=', $request->from);
            })
            ->when($request->filled('to'), function ($query) use ($request) {
                $query->whereDate('expense_date', '<=', $request->to);
            })
            ->orderByDesc('expense_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $totalFiltered = (clone $expenses)->sum('amount');

        return view('laser.expenses.index', [
            'expenses' => $expenses,
            'categories' => LaserExpenseCategory::orderBy('name')->get(),
            'totalFiltered' => $totalFiltered,
        ]);
    }

    public function create()
    {
        return view('laser.expenses.create', [
            'categories' => LaserExpenseCategory::orderBy('name')->get(),
            'methods' => LaserExpense::methods(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        $expense = LaserExpense::create(array_merge($validated, [
            'created_by' => auth()->id(),
        ]));

        return redirect()
            ->route('laser.expenses.index')
            ->with('success', "Laser expense '{$expense->title}' (PKR ".number_format($expense->amount, 2).') recorded.');
    }

    public function edit(LaserExpense $expense)
    {
        return view('laser.expenses.edit', [
            'expense' => $expense,
            'categories' => LaserExpenseCategory::orderBy('name')->get(),
            'methods' => LaserExpense::methods(),
        ]);
    }

    public function update(Request $request, LaserExpense $expense)
    {
        $validated = $request->validate($this->rules());

        $expense->update($validated);

        return redirect()
            ->route('laser.expenses.index')
            ->with('success', "Laser expense '{$expense->title}' updated successfully.");
    }

    public function destroy(LaserExpense $expense)
    {
        $expense->delete();

        return redirect()
            ->route('laser.expenses.index')
            ->with('success', 'Laser expense deleted successfully.');
    }
}
