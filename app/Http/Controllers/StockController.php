<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\StockTransaction;
use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\ExpenseCategory;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $transactions = StockTransaction::with(['medicine', 'supplier', 'createdBy'])
            ->when($request->filled('medicine_id'), function ($query) use ($request) {
                $query->where('medicine_id', $request->medicine_id);
            })
            ->when($request->filled('type'), function ($query) use ($request) {
                $query->where('type', $request->type);
            })
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        // Same whereColumn comparison as the medicines list filter
        $lowStockCount = Medicine::whereColumn('stock_quantity', '<=', 'minimum_stock')->count();

        return view('stock.index', [
            'transactions'  => $transactions,
            'medicines'     => Medicine::orderBy('name')->get(), // filter dropdown
            'lowStockCount' => $lowStockCount,
        ]);
    }

       public function create(Request $request)
    {
        return view('stock.create', [
            'medicines'  => Medicine::where('status', 'active')->orderBy('name')->get(),
            'suppliers'  => Supplier::where('status', 'active')->orderBy('name')->get(),
            'medicineId' => $request->query('medicine_id'),
            'type'       => $request->query('type') === 'out' ? 'out' : 'in',

            // For the "also record as expense" option
            'expenseCategories'        => ExpenseCategory::where('status', 'active')->orderBy('name')->get(),
            'defaultExpenseCategoryId' => ExpenseCategory::where('name', 'Medicine Purchase')
                                                ->where('status', 'active')
                                                ->value('id'),
        ]);
    }
       public function store(Request $request)
    {
        $validated = $request->validate([
            'medicine_id'      => 'required|exists:medicines,id',
            'type'             => 'required|in:in,out',
            'quantity'         => 'required|integer|min:1',
            'transaction_date' => 'required|date',
            'supplier_id'      => 'nullable|exists:suppliers,id',
            // required_with: if "record as expense" is checked, a unit cost is mandatory
            'unit_cost'        => 'nullable|numeric|min:0|required_with:record_expense',
            'reference'        => 'nullable|string|max:255',
            'notes'            => 'nullable|string|max:2000',

            // Purchase-as-expense fields
            'record_expense'         => 'nullable|boolean',
            'expense_category_id'    => 'nullable|exists:expense_categories,id|required_with:record_expense',
            'expense_payment_method' => 'nullable|in:cash,bank,card,online,other',
        ]);

        $medicine = Medicine::findOrFail($validated['medicine_id']);
        $quantity = (int) $validated['quantity'];

        if ($validated['type'] === 'out') {
            // STOCK GUARD: stock can never go negative
            if ($quantity > (int) $medicine->stock_quantity) {
                return back()
                    ->withErrors(['quantity' =>
                        'Cannot remove ' . $quantity . '; "' . $medicine->name . '" only has ' .
                        $medicine->stock_quantity . ' ' . ($medicine->unit ?? 'units') . ' in stock.'])
                    ->withInput();
            }

            $medicine->stock_quantity -= $quantity;
        } else {
            $medicine->stock_quantity += $quantity;
        }

        $medicine->save();

        // Optionally auto-create the purchase expense FIRST, so we can link it
        $expenseId = null;
        if ($validated['type'] === 'in' && $request->boolean('record_expense')) {
            $expenseId = Expense::create([
                'expense_category_id' => $validated['expense_category_id'],
                'title'               => 'Medicine Purchase — ' . $medicine->name,
                'amount'              => $quantity * (float) $validated['unit_cost'],
                'expense_date'        => $validated['transaction_date'],
                'payment_method'      => $validated['expense_payment_method'] ?? Expense::METHOD_CASH,
                'reference'           => $validated['reference'] ?? null,
                'description'         => 'Auto-created from Stock In: ' . $quantity .
                                         ' × ' . $medicine->name .
                                         ' @ ' . number_format((float) $validated['unit_cost'], 2),
                'created_by'          => auth()->id(),
            ])->id;
        }

        StockTransaction::create([
            'medicine_id'      => $medicine->id,
            'type'             => $validated['type'],
            'quantity'         => $quantity,
            'transaction_date' => $validated['transaction_date'],
            'supplier_id'      => $validated['type'] === 'in' ? ($validated['supplier_id'] ?? null) : null,
            'unit_cost'        => $validated['type'] === 'in' ? ($validated['unit_cost'] ?? null) : null,
            'reference'        => $validated['reference'] ?? null,
            'notes'            => $validated['notes'] ?? null,
            'expense_id'       => $expenseId,
            'created_by'       => auth()->id(),
        ]);

        $message = 'Stock ' . ($validated['type'] === 'in' ? 'added to' : 'removed from') .
            ' "' . $medicine->name . '". New stock: ' . $medicine->stock_quantity . '.' .
            ($expenseId ? ' Expense recorded automatically.' : '');

        if ($request->input('redirect') === 'medicine') {
            return redirect()
                ->to(route('medicines.show', $medicine) . '#stock-history')
                ->with('success', $message);
        }

        return redirect()->route('stock.index')->with('success', $message);
    }

        public function destroy(Request $request, StockTransaction $stock)
    {
        $medicine = $stock->medicine;
        $quantity = (int) $stock->quantity;

        if ($stock->type === StockTransaction::TYPE_IN) {
            // Reversing a stock-in: those units must still be in stock
            if ($quantity > (int) $medicine->stock_quantity) {
                $message = 'Cannot delete this transaction: "' . $medicine->name .
                    '" no longer holds ' . $quantity . ' units. Correct it with a Stock Out instead.';

                if ($request->query('redirect') === 'medicine') {
                    return redirect()
                        ->to(route('medicines.show', $medicine) . '#stock-history')
                        ->with('error', $message);
                }

                return redirect()->route('stock.index')->with('error', $message);
            }

            $medicine->stock_quantity -= $quantity; // reverse the stock-in
        } else {
            $medicine->stock_quantity += $quantity; // reverse the stock-out
        }

        $medicine->save();

        // If this purchase auto-created an expense, remove it too (full reversal)
        $expenseDeleted = false;
        if ($stock->expense_id) {
            Expense::find($stock->expense_id)?->delete();
            $expenseDeleted = true;
        }

        $stock->delete();

        $message = 'Transaction deleted. Stock of "' . $medicine->name . '" adjusted to ' .
            $medicine->stock_quantity . '.' .
            ($expenseDeleted ? ' The linked expense was also removed.' : '');

        if ($request->query('redirect') === 'medicine') {
            return redirect()
                ->to(route('medicines.show', $medicine) . '#stock-history')
                ->with('success', $message);
        }

        return redirect()->route('stock.index')->with('success', $message);
    }
}
