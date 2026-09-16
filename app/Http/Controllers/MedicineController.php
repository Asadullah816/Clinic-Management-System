<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\MedicineCategory;
use App\Models\Supplier;
use Illuminate\Http\Request;

class MedicineController extends Controller
{
    private function rules(): array
    {
        return [
            'name'                 => 'required|string|max:255',
            'generic_name'         => 'nullable|string|max:255',
            'medicine_category_id' => 'required|exists:medicine_categories,id',
            'supplier_id'          => 'nullable|exists:suppliers,id',
            'unit'                 => 'nullable|string|max:20',
            'purchase_price'       => 'required|numeric|min:0',
            'selling_price'        => 'required|numeric|min:0',
            'stock_quantity'       => 'required|integer|min:0',
            'minimum_stock'        => 'required|integer|min:0',
            'expiry_date'          => 'nullable|date',
            'description'          => 'nullable|string|max:2000',
            'status'               => 'required|in:active,inactive',
        ];
    }

    public function index(Request $request)
    {
        $medicines = Medicine::with(['category', 'supplier'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('generic_name', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('category_id'), function ($query) use ($request) {
                $query->where('medicine_category_id', $request->category_id);
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            // Compare two columns against each other — stock at or below minimum
            ->when($request->boolean('low_stock'), function ($query) {
                $query->whereColumn('stock_quantity', '<=', 'minimum_stock');
            })
            ->when($request->boolean('expired'), function ($query) {
                $query->whereDate('expiry_date', '<', today());
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('medicines.index', [
            'medicines'  => $medicines,
            'categories' => MedicineCategory::orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('medicines.create', [
            'categories' => MedicineCategory::where('status', 'active')->orderBy('name')->get(),
            'suppliers'  => Supplier::where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate($this->rules());

        Medicine::create($request->all());

        return redirect()
            ->route('medicines.index')
            ->with('success', 'Product created successfully.');
    }

        public function show(Medicine $medicine)
    {
        $medicine->load([
            'category',
            'supplier',
            'stockTransactions' => function ($query) {
                $query->with(['supplier', 'createdBy'])
                      ->orderByDesc('transaction_date')
                      ->orderByDesc('id');
            },
            'medicineUsages' => function ($query) {
                $query->with(['patient', 'treatment', 'usedBy'])
                      ->orderByDesc('usage_date')
                      ->orderByDesc('id');
            },
        ]);

        return view('medicines.show', [
            'medicine' => $medicine,
        ]);
    }

    public function edit(Medicine $medicine)
    {
        return view('medicines.edit', [
            'medicine'   => $medicine,
            'categories' => MedicineCategory::where('status', 'active')->orderBy('name')->get(),
            'suppliers'  => Supplier::where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Medicine $medicine)
    {
        $request->validate($this->rules());

        $medicine->update($request->all());

        return redirect()
            ->route('medicines.show', $medicine)
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Medicine $medicine)
    {
        $medicine->delete(); // soft delete — historical usage/stock records keep the name

        return redirect()
            ->route('medicines.index')
            ->with('success', 'Product deleted successfully.');
    }
}
