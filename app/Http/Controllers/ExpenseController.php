<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    private function rules(): array
    {
        return [
            'expense_category_id' => 'required|exists:expense_categories,id',
            'title'               => 'required|string|max:255',
            'amount'              => 'required|numeric|min:0.01',
            'expense_date'        => 'required|date',
            'payment_method'      => 'required|in:cash,bank,card,online,other',
            'reference'           => 'nullable|string|max:255',
            'description'         => 'nullable|string|max:2000',
        ];
    }

        public function index(Request $request)
    {
        $period = $request->query('period', 'all');
        [$periodStart, $periodEnd, $periodLabel] = $this->periodRange($period);

        $query = Expense::with(['expenseCategory', 'createdBy'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->where(function ($sub) use ($search) {
                    $sub->where('title', 'like', "%{$search}%")
                        ->orWhere('reference', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('category_id'), function ($query) use ($request) {
                $query->where('expense_category_id', $request->category_id);
            })
            ->when($periodStart, function ($query) use ($periodStart, $periodEnd) {
                $query->whereBetween('expense_date', [$periodStart, $periodEnd]);
            });

        // Clone = same filters (including period), one number
        $filteredTotal = (clone $query)->sum('amount');

        $expenses = $query->orderByDesc('expense_date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('expenses.index', [
            'expenses'      => $expenses,
            'categories'    => ExpenseCategory::orderBy('name')->get(),
            'filteredTotal' => $filteredTotal,
            'periods'       => $this->periods(),
            'periodLabel'   => $periodLabel,
        ]);
    }

    public function create()
    {
        return view('expenses.create', [
            'categories' => ExpenseCategory::where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        Expense::create($validated + ['created_by' => auth()->id()]);

        return redirect()
            ->route('expenses.index')
            ->with('success', 'Expense recorded successfully.');
    }

    public function edit(Expense $expense)
    {
        return view('expenses.edit', [
            'expense'    => $expense,
            'categories' => ExpenseCategory::where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate($this->rules());

        $expense->update($validated);

        return redirect()
            ->route('expenses.index')
            ->with('success', 'Expense updated successfully.');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete(); // hard delete — expenses are transactional records

        return redirect()
            ->route('expenses.index')
            ->with('success', 'Expense deleted successfully.');
    }
}
