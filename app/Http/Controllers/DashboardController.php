<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Payment;
use Illuminate\Http\Request;   // ← THIS was missing

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // ---------- Period filter (defaults to This Month) ----------
        $period = in_array($request->query('period'), array_keys($this->periods()))
            ? $request->query('period')
            : 'month';

        [$periodStart, $periodEnd, $periodLabel] = $this->periodRange($period);

        // ================= Patient statistics (all roles) =================
        $totalPatients  = Patient::count();
        $todaysPatients = Patient::whereDate('created_at', today())->count();
        $monthsPatients = Patient::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $todaysAppointments = Appointment::with(['patient', 'treatment'])
            ->whereDate('appointment_date', today())
            ->orderBy('appointment_time')
            ->get();

        $showAppointments = $user->hasRole('admin', 'receptionist', 'staff');

        // ================= Financial (admin, accountant) — period-scoped =================
        $showFinancial = $user->hasRole('admin', 'accountant');
        $periodRevenue  = 0;
        $periodExpenses = 0;
        $periodProfit   = 0;
        $totalOutstanding = 0;

        if ($showFinancial) {
            $revenueQuery = Payment::query();
            $expenseQuery = Expense::query();

            // 'all' → no date conditions, sums everything
            if ($periodStart) {
                $revenueQuery->whereBetween('payment_date', [$periodStart, $periodEnd]);
                $expenseQuery->whereBetween('expense_date', [$periodStart, $periodEnd]);
            }

            $periodRevenue  = $revenueQuery->sum('amount');
            $periodExpenses = $expenseQuery->sum('amount');

            // THE formula, now scoped to the selected period
            $periodProfit = $periodRevenue - $periodExpenses;

            // Outstanding is a lifetime balance — never period-scoped
            $totalOutstanding = Invoice::where('due_amount', '>', 0)->sum('due_amount');
        }

        // ================= Inventory (admin, staff) =================
        $showInventory = $user->hasRole('admin', 'staff');
        $totalProducts = 0;
        $lowStockCount = 0;
        $expiredCount  = 0;

        if ($showInventory) {
            $totalProducts = Medicine::count();
            $lowStockCount = Medicine::whereColumn('stock_quantity', '<=', 'minimum_stock')->count();
            $expiredCount  = Medicine::whereDate('expiry_date', '<', today())->count();
        }

        return view('dashboard.index', [
            'showAppointments'   => $showAppointments,
            'showFinancial'      => $showFinancial,
            'showInventory'      => $showInventory,

            'totalPatients'      => $totalPatients,
            'todaysPatients'     => $todaysPatients,
            'monthsPatients'     => $monthsPatients,
            'todaysAppointments' => $todaysAppointments,

            'period'             => $period,
            'periodLabel'        => $periodLabel,
            'periods'            => $this->periods(),
            'periodRevenue'      => $periodRevenue,
            'periodExpenses'     => $periodExpenses,
            'periodProfit'       => $periodProfit,
            'totalOutstanding'   => $totalOutstanding,

            'totalProducts'      => $totalProducts,
            'lowStockCount'      => $lowStockCount,
            'expiredCount'       => $expiredCount,
        ]);
    }
}
