<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\User;
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

        // ================= Patient statistics (scoped for doctors) =================
        $patientQuery = Patient::query();
        if ($user->isDoctor()) {
            $patientQuery->forDoctor($user->id);
        }
        $totalPatients = (clone $patientQuery)->count();
        $todaysPatients = (clone $patientQuery)->whereDate('created_at', today())->count();
        $monthsPatients = (clone $patientQuery)->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Appointments filter on Dashboard
        $apptDate = $request->query('appt_date') ?: today()->toDateString();
        $apptStatus = $request->query('appt_status');
        $apptSearch = $request->query('appt_search');
        $apptDoctorId = $request->query('appt_doctor_id');

        $todaysAppointments = Appointment::with(['patient', 'treatment', 'treatments', 'doctor'])
            ->whereDate('appointment_date', $apptDate)
            ->when($user->isDoctor(), function ($q) use ($user) {
                $q->where('doctor_id', $user->id);
            })
            ->when(! $user->isDoctor() && $apptDoctorId, function ($q) use ($apptDoctorId) {
                $q->where('doctor_id', $apptDoctorId);
            })
            ->when($apptStatus, function ($q) use ($apptStatus) {
                $q->where('status', $apptStatus);
            })
            ->when($apptSearch, function ($q) use ($apptSearch) {
                $q->whereHas('patient', function ($p) use ($apptSearch) {
                    $p->where('first_name', 'like', "%{$apptSearch}%")
                        ->orWhere('last_name', 'like', "%{$apptSearch}%")
                        ->orWhere('patient_number', 'like', "%{$apptSearch}%")
                        ->orWhere('phone', 'like', "%{$apptSearch}%");
                });
            })
            ->orderBy('appointment_time')
            ->get();

        $todayActualCount = Appointment::whereDate('appointment_date', today())
            ->when($user->isDoctor(), function ($q) use ($user) {
                $q->where('doctor_id', $user->id);
            })
            ->count();

        $showAppointments = $user->hasRole('admin', 'doctor', 'receptionist', 'staff');

        // ================= Financial (admin, accountant) — period-scoped =================
        $showFinancial = $user->hasRole('admin', 'accountant');
        $periodRevenue = 0;
        $periodExpenses = 0;
        $periodProfit = 0;
        $totalOutstanding = 0;

        if ($showFinancial) {
            $revenueQuery = Payment::query();
            $expenseQuery = Expense::query();

            // 'all' → no date conditions, sums everything
            if ($periodStart) {
                $revenueQuery->whereBetween('payment_date', [$periodStart, $periodEnd]);
                $expenseQuery->whereBetween('expense_date', [$periodStart, $periodEnd]);
            }

            $periodRevenue = $revenueQuery->sum('amount');
            $periodExpenses = $expenseQuery->sum('amount');

            // THE formula, now scoped to the selected period
            $periodProfit = $periodRevenue - $periodExpenses;

            // Outstanding is a lifetime balance — never period-scoped
            $totalOutstanding = Invoice::where('due_amount', '>', 0)->sum('due_amount');
        }

        // ================= Inventory (admin, staff) - Hidden for now =================
        $showInventory = false;
        $totalProducts = 0;
        $lowStockCount = 0;
        $expiredCount = 0;

        if ($showInventory && $user->hasRole('admin', 'staff')) {
            $totalProducts = Medicine::count();
            $lowStockCount = Medicine::whereColumn('stock_quantity', '<=', 'minimum_stock')->count();
            $expiredCount = Medicine::whereDate('expiry_date', '<', today())->count();
        }

        return view('dashboard.index', [
            'showAppointments' => $showAppointments,
            'showFinancial' => $showFinancial,
            'showInventory' => $showInventory,

            'totalPatients' => $totalPatients,
            'todaysPatients' => $todaysPatients,
            'monthsPatients' => $monthsPatients,
            'todaysAppointments' => $todaysAppointments,
            'todayActualCount' => $todayActualCount,
            'apptDate' => $apptDate,
            'apptStatus' => $apptStatus,
            'apptSearch' => $apptSearch,
            'apptDoctorId' => $apptDoctorId,
            'doctors' => User::whereIn('role', [User::ROLE_DOCTOR, User::ROLE_ADMIN])->orderBy('name')->get(),

            'period' => $period,
            'periodLabel' => $periodLabel,
            'periods' => $this->periods(),
            'periodRevenue' => $periodRevenue,
            'periodExpenses' => $periodExpenses,
            'periodProfit' => $periodProfit,
            'totalOutstanding' => $totalOutstanding,

            'totalProducts' => $totalProducts,
            'lowStockCount' => $lowStockCount,
            'expiredCount' => $expiredCount,
        ]);
    }
}
