<?php

namespace App\Http\Controllers;

use App\Models\LaserExpense;
use App\Models\LaserExpenseCategory;
use App\Models\LaserSession;
use App\Models\LaserTreatment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaserReportController extends Controller
{
    private function dateRange(Request $request): array
    {
        $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date',
        ]);

        return [$request->filled('from'), $request->from, $request->to];
    }

    /**
     * Financial Summary of the Shared Laser Unit (Revenue - Expenses = Shared Net Profit)
     */
    public function financial(Request $request)
    {
        [$hasRange, $from, $to] = $this->dateRange($request);

        $sessionsQuery = LaserSession::query()
            ->when($hasRange && $from, fn ($q) => $q->whereDate('session_date', '>=', $from))
            ->when($hasRange && $to, fn ($q) => $q->whereDate('session_date', '<=', $to));

        $expensesQuery = LaserExpense::query()
            ->when($hasRange && $from, fn ($q) => $q->whereDate('expense_date', '>=', $from))
            ->when($hasRange && $to, fn ($q) => $q->whereDate('expense_date', '<=', $to));

        $totalRevenue = (float) (clone $sessionsQuery)->sum('paid_amount');
        $totalBilled = (float) (clone $sessionsQuery)->sum('total_amount');
        $totalDiscounts = (float) (clone $sessionsQuery)->sum('discount');
        $totalOutstanding = (float) (clone $sessionsQuery)->where('due_amount', '>', 0)->sum('due_amount');
        $totalExpenses = (float) (clone $expensesQuery)->sum('amount');
        $netProfit = $totalRevenue - $totalExpenses;

        // Breakdown of expenses by category
        $expensesByCategory = (clone $expensesQuery)
            ->join('laser_expense_categories', 'laser_expenses.laser_expense_category_id', '=', 'laser_expense_categories.id')
            ->select('laser_expense_categories.name', DB::raw('SUM(laser_expenses.amount) as total'))
            ->groupBy('laser_expense_categories.name')
            ->orderByDesc('total')
            ->get();

        // Breakdown of payments by method
        $revenueByMethod = (clone $sessionsQuery)
            ->select('payment_method', DB::raw('SUM(paid_amount) as total'))
            ->whereNotNull('payment_method')
            ->where('paid_amount', '>', 0)
            ->groupBy('payment_method')
            ->get();

        $profitMargin = $totalRevenue > 0 ? round(($netProfit / $totalRevenue) * 100, 1) : 0;
        $sessionsCount = (clone $sessionsQuery)->count();
        $expensesCount = (clone $expensesQuery)->count();

        return view('laser.reports.financial', [
            'totalRevenue' => $totalRevenue,
            'totalBilled' => $totalBilled,
            'totalDiscounts' => $totalDiscounts,
            'totalOutstanding' => $totalOutstanding,
            'totalExpenses' => $totalExpenses,
            'netProfit' => $netProfit,
            'profitMargin' => $profitMargin,
            'sessionsCount' => $sessionsCount,
            'expensesCount' => $expensesCount,
            'expensesByCategory' => $expensesByCategory,
            'revenueByMethod' => $revenueByMethod,
            'from' => $from,
            'to' => $to,
        ]);
    }

    /**
     * Laser Procedure Volume and Session Breakdown
     */
    public function sessions(Request $request)
    {
        [$hasRange, $from, $to] = $this->dateRange($request);

        $query = LaserSession::with(['patient', 'treatment', 'performedBy'])
            ->when($request->filled('treatment_id'), fn ($q) => $q->where('laser_treatment_id', $request->treatment_id))
            ->when($hasRange && $from, fn ($q) => $q->whereDate('session_date', '>=', $from))
            ->when($hasRange && $to, fn ($q) => $q->whereDate('session_date', '<=', $to));

        $totalPaid = (clone $query)->sum('paid_amount');
        $totalSessions = (clone $query)->count();

        $sessions = $query->orderByDesc('session_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('laser.reports.sessions', [
            'sessions' => $sessions,
            'totalPaid' => $totalPaid,
            'totalSessions' => $totalSessions,
            'treatments' => LaserTreatment::orderBy('name')->get(),
            'from' => $from,
            'to' => $to,
        ]);
    }

    /**
     * Laser Expenses Report
     */
    public function expenses(Request $request)
    {
        [$hasRange, $from, $to] = $this->dateRange($request);

        $query = LaserExpense::with(['expenseCategory', 'createdBy'])
            ->when($request->filled('category_id'), fn ($q) => $q->where('laser_expense_category_id', $request->category_id))
            ->when($hasRange && $from, fn ($q) => $q->whereDate('expense_date', '>=', $from))
            ->when($hasRange && $to, fn ($q) => $q->whereDate('expense_date', '<=', $to));

        $totalAmount = (clone $query)->sum('amount');

        $expenses = $query->orderByDesc('expense_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('laser.reports.expenses', [
            'expenses' => $expenses,
            'totalAmount' => $totalAmount,
            'categories' => LaserExpenseCategory::orderBy('name')->get(),
            'from' => $from,
            'to' => $to,
        ]);
    }
}
