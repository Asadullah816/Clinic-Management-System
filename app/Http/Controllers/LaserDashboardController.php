<?php

namespace App\Http\Controllers;

use App\Models\LaserExpense;
use App\Models\LaserPatient;
use App\Models\LaserSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaserDashboardController extends Controller
{
    protected function periods(): array
    {
        return [
            'today' => 'Today',
            'week' => 'This Week',
            'month' => 'This Month',
            'year' => 'This Year',
            'all' => 'All Time',
        ];
    }

    protected function periodRange(?string $period): array
    {
        return match ($period) {
            'today' => [today()->startOfDay(), today()->endOfDay(), 'Today'],
            'week' => [now()->startOfWeek(), now()->endOfWeek(), 'This Week'],
            'year' => [now()->startOfYear(), now()->endOfYear(), 'This Year'],
            'all' => [null, null, 'All Time'],
            default => [now()->startOfMonth(), now()->endOfMonth(), 'This Month'],
        };
    }

    public function index(Request $request)
    {
        $period = in_array($request->query('period'), array_keys($this->periods()))
            ? $request->query('period')
            : 'month';

        [$periodStart, $periodEnd, $periodLabel] = $this->periodRange($period);

        // Period-filtered queries
        $sessionQuery = LaserSession::query();
        $expenseQuery = LaserExpense::query();

        if ($periodStart) {
            $sessionQuery->whereBetween('session_date', [$periodStart, $periodEnd]);
            $expenseQuery->whereBetween('expense_date', [$periodStart, $periodEnd]);
        }

        // Period metrics
        $periodRevenue = (float) (clone $sessionQuery)->sum('paid_amount');
        $periodBilled = (float) (clone $sessionQuery)->sum('total_amount');
        $periodExpenses = (float) (clone $expenseQuery)->sum('amount');
        $periodDiscounts = (float) (clone $sessionQuery)->sum('discount');
        $periodProfit = $periodRevenue - $periodExpenses;
        $periodSessionsCount = (clone $sessionQuery)->count();
        $profitMargin = $periodRevenue > 0 ? round(($periodProfit / $periodRevenue) * 100, 1) : 0;

        // Lifetime metrics
        $totalPatientsCount = LaserPatient::count();
        $totalSessionsCount = LaserSession::count();
        $lifetimeRevenue = (float) LaserSession::sum('paid_amount');
        $lifetimeExpenses = (float) LaserExpense::sum('amount');
        $lifetimeProfit = $lifetimeRevenue - $lifetimeExpenses;
        $totalOutstandingDue = (float) LaserSession::where('due_amount', '>', 0)->sum('due_amount');
        $todaySessionsCount = LaserSession::whereDate('session_date', today())->count();

        // Top procedures performed in this period
        $topProcedures = (clone $sessionQuery)
            ->join('laser_treatments', 'laser_sessions.laser_treatment_id', '=', 'laser_treatments.id')
            ->select('laser_treatments.name', 'laser_treatments.body_area', DB::raw('COUNT(laser_sessions.id) as count'), DB::raw('SUM(laser_sessions.paid_amount) as revenue'))
            ->groupBy('laser_treatments.id', 'laser_treatments.name', 'laser_treatments.body_area')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        // Top expense categories in this period
        $topExpenseCategories = (clone $expenseQuery)
            ->join('laser_expense_categories', 'laser_expenses.laser_expense_category_id', '=', 'laser_expense_categories.id')
            ->select('laser_expense_categories.name', DB::raw('SUM(laser_expenses.amount) as total'))
            ->groupBy('laser_expense_categories.id', 'laser_expense_categories.name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // Recent 8 laser sessions
        $recentSessions = LaserSession::with(['patient', 'treatment', 'performedBy'])
            ->orderByDesc('session_date')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        return view('laser.dashboard', [
            'period' => $period,
            'periodLabel' => $periodLabel,
            'periods' => $this->periods(),
            'periodRevenue' => $periodRevenue,
            'periodBilled' => $periodBilled,
            'periodExpenses' => $periodExpenses,
            'periodDiscounts' => $periodDiscounts,
            'periodProfit' => $periodProfit,
            'profitMargin' => $profitMargin,
            'periodSessionsCount' => $periodSessionsCount,
            'todaySessionsCount' => $todaySessionsCount,
            'totalPatientsCount' => $totalPatientsCount,
            'totalSessionsCount' => $totalSessionsCount,
            'lifetimeRevenue' => $lifetimeRevenue,
            'lifetimeExpenses' => $lifetimeExpenses,
            'lifetimeProfit' => $lifetimeProfit,
            'totalOutstandingDue' => $totalOutstandingDue,
            'topProcedures' => $topProcedures,
            'topExpenseCategories' => $topExpenseCategories,
            'recentSessions' => $recentSessions,
        ]);
    }
}
