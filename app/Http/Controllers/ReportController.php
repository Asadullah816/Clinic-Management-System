<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Invoice;
use App\Models\Medicine;
use App\Models\MedicineUsage;
use App\Models\Patient;
use App\Models\Payment;
use Illuminate\Http\Request;   // ← THE FIX: this line was missing

class ReportController extends Controller
{
    /**
     * Common date filters for the list reports.
     * Returns [filters-applied?, from, to] — kept tiny and shared.
     */
    private function dateRange(Request $request): array
    {
        $request->validate([
            'from' => 'nullable|date',
            'to'   => 'nullable|date',
        ]);

        return [$request->filled('from'), $request->from, $request->to];
    }

    /**
     * Patient Report: per-patient visits, treatment cost, paid, due.
     */
    public function patients(Request $request)
    {
        $patients = Patient::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('patient_number', 'like', "%{$search}%");
                });
            })
            // Count only COMPLETED appointments as visits (aliased to completed_visits)
            ->withCount(['appointments as completed_visits' => function ($query) {
                $query->where('status', Appointment::STATUS_COMPLETED);
            }])
            // Sum columns of related tables, aliased for readable view variables
            ->withSum('patientTreatments as treatment_cost_total', 'total_amount')
            ->withSum('invoices as paid_total', 'paid_amount')
            ->withSum('invoices as due_total', 'due_amount')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->paginate(15)
            ->withQueryString();

        return view('reports.patients', ['patients' => $patients]);
    }

    /**
     * Payment Report: payments in a date range, with a filtered total.
     */
    public function payments(Request $request)
    {
        [$hasRange, $from, $to] = $this->dateRange($request);

        $query = Payment::with(['patient', 'invoice', 'receivedBy'])
            ->when($request->filled('payment_method'), function ($q) use ($request) {
                $q->where('payment_method', $request->payment_method);
            })
            ->when($hasRange && $from, function ($q) use ($from) {
                $q->whereDate('payment_date', '>=', $from);
            })
            ->when($hasRange && $to, function ($q) use ($to) {
                $q->whereDate('payment_date', '<=', $to);
            });

        // Clone = same filters, one number
        $filteredTotal = (clone $query)->sum('amount');

        $payments = $query->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('reports.payments', [
            'payments'      => $payments,
            'filteredTotal' => $filteredTotal,
        ]);
    }

    /**
     * Expense Report: expenses in a date range / category, with a filtered total.
     */
    public function expenses(Request $request)
    {
        [$hasRange, $from, $to] = $this->dateRange($request);

        $query = Expense::with(['expenseCategory', 'createdBy'])
            ->when($request->filled('category_id'), function ($q) use ($request) {
                $q->where('expense_category_id', $request->category_id);
            })
            ->when($hasRange && $from, function ($q) use ($from) {
                $q->whereDate('expense_date', '>=', $from);
            })
            ->when($hasRange && $to, function ($q) use ($to) {
                $q->whereDate('expense_date', '<=', $to);
            });

        $filteredTotal = (clone $query)->sum('amount');

        $expenses = $query->orderByDesc('expense_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('reports.expenses', [
            'expenses'      => $expenses,
            'categories'    => ExpenseCategory::orderBy('name')->get(),
            'filteredTotal' => $filteredTotal,
        ]);
    }

    /**
     * Inventory Report: products with stock levels and expiry.
     */
    public function inventory(Request $request)
    {
        $medicines = Medicine::with('category')
            ->when($request->boolean('low_stock'), function ($query) {
                $query->whereColumn('stock_quantity', '<=', 'minimum_stock');
            })
            ->when($request->boolean('expired'), function ($query) {
                $query->whereDate('expiry_date', '<', today());
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('reports.inventory', ['medicines' => $medicines]);
    }

    /**
     * Medicine Usage Report: what was used, on whom, by whom.
     */
    public function usage(Request $request)
    {
        [$hasRange, $from, $to] = $this->dateRange($request);

        $query = MedicineUsage::with(['patient', 'treatment', 'medicine', 'usedBy'])
            ->when($request->filled('patient_id'), function ($q) use ($request) {
                $q->where('patient_id', $request->patient_id);
            })
            ->when($request->filled('medicine_id'), function ($q) use ($request) {
                $q->where('medicine_id', $request->medicine_id);
            })
            ->when($hasRange && $from, function ($q) use ($from) {
                $q->whereDate('usage_date', '>=', $from);
            })
            ->when($hasRange && $to, function ($q) use ($to) {
                $q->whereDate('usage_date', '<=', $to);
            });

        $filteredQuantity = (clone $query)->sum('quantity');

        $usages = $query->orderByDesc('usage_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('reports.usage', [
            'usages'           => $usages,
            'patients'         => Patient::orderBy('first_name')->orderBy('last_name')->get(),
            'medicines'        => Medicine::withTrashed()->orderBy('name')->get(),
            'filteredQuantity' => $filteredQuantity,
        ]);
    }

    /**
     * Financial Summary: the four numbers from the spec, lifetime.
     */
    public function financial()
    {
        // Revenue = money RECEIVED (same definition as the dashboard)
        $totalRevenue = Payment::sum('amount');
        $totalExpenses = Expense::sum('amount');
        $totalOutstanding = Invoice::where('due_amount', '>', 0)->sum('due_amount');

        // THE formula: Revenue - Expenses = Profit
        $netProfit = $totalRevenue - $totalExpenses;

        return view('reports.financial', [
            'totalRevenue'     => $totalRevenue,
            'totalExpenses'    => $totalExpenses,
            'totalOutstanding' => $totalOutstanding,
            'netProfit'        => $netProfit,
        ]);
    }
}
