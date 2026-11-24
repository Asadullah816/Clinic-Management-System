<?php

namespace App\Http\Controllers;

use App\Models\Implant;
use App\Models\ImplantStockTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ImplantController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $brandFilter = $request->query('brand');
        $stockFilter = $request->query('stock_status');

        $query = Implant::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('item_code', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('diameter', 'like', "%{$search}%")
                    ->orWhere('length', 'like', "%{$search}%")
                    ->orWhere('lot_number', 'like', "%{$search}%");
            });
        }

        if ($brandFilter) {
            $query->where('brand', $brandFilter);
        }

        if ($stockFilter === 'out_of_stock') {
            $query->where('stock_quantity', '<=', 0);
        } elseif ($stockFilter === 'low_stock') {
            $query->where('stock_quantity', '>', 0)
                ->whereRaw('stock_quantity <= min_stock_alert');
        } elseif ($stockFilter === 'in_stock') {
            $query->where('stock_quantity', '>', 0);
        }

        $implants = $query->orderBy('name')->paginate(15)->withQueryString();

        $brands = Implant::whereNotNull('brand')->where('brand', '!=', '')->distinct()->pluck('brand');

        $stats = [
            'total_models' => Implant::count(),
            'total_stock' => (int) Implant::sum('stock_quantity'),
            'low_stock' => Implant::where('stock_quantity', '>', 0)->whereRaw('stock_quantity <= min_stock_alert')->count(),
            'out_of_stock' => Implant::where('stock_quantity', '<=', 0)->count(),
            'total_value' => (float) Implant::selectRaw('SUM(stock_quantity * purchase_price) as val')->value('val') ?? 0,
        ];

        return view('implants.index', compact('implants', 'brands', 'stats', 'search', 'brandFilter', 'stockFilter'));
    }

    public function create()
    {
        $brands = Implant::whereNotNull('brand')->where('brand', '!=', '')->distinct()->pluck('brand');

        return view('implants.create', compact('brands'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_code' => 'nullable|string|max:50|unique:implants,item_code',
            'name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:100',
            'diameter' => 'nullable|string|max:50',
            'length' => 'nullable|string|max:50',
            'platform' => 'nullable|string|max:100',
            'connection_type' => 'nullable|string|max:100',
            'material' => 'nullable|string|max:100',
            'lot_number' => 'nullable|string|max:100',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'min_stock_alert' => 'required|integer|min:0',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        if (empty($validated['item_code'])) {
            $nextId = (Implant::max('id') ?? 0) + 1;
            $validated['item_code'] = 'IMP-'.str_pad($nextId, 4, '0', STR_PAD_LEFT);
        }

        DB::transaction(function () use ($validated) {
            $implant = Implant::create($validated);

            if ($implant->stock_quantity > 0) {
                ImplantStockTransaction::create([
                    'implant_id' => $implant->id,
                    'type' => 'initial',
                    'quantity' => $implant->stock_quantity,
                    'balance_after' => $implant->stock_quantity,
                    'notes' => 'Initial stock registered',
                    'created_by' => auth()->id(),
                ]);
            }
        });

        return redirect()->route('implants.index')->with('success', 'Dental Implant created successfully.');
    }

    public function show(Implant $implant)
    {
        $implant->load(['stockTransactions.createdBy', 'saleItems.sale.buyer']);

        return view('implants.show', compact('implant'));
    }

    public function edit(Implant $implant)
    {
        $brands = Implant::whereNotNull('brand')->where('brand', '!=', '')->distinct()->pluck('brand');

        return view('implants.edit', compact('implant', 'brands'));
    }

    public function update(Request $request, Implant $implant)
    {
        $validated = $request->validate([
            'item_code' => 'required|string|max:50|unique:implants,item_code,'.$implant->id,
            'name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:100',
            'diameter' => 'nullable|string|max:50',
            'length' => 'nullable|string|max:50',
            'platform' => 'nullable|string|max:100',
            'connection_type' => 'nullable|string|max:100',
            'material' => 'nullable|string|max:100',
            'lot_number' => 'nullable|string|max:100',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'min_stock_alert' => 'required|integer|min:0',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $implant->update($validated);

        return redirect()->route('implants.show', $implant)->with('success', 'Dental Implant updated successfully.');
    }

    public function restock(Request $request, Implant $implant)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'purchase_price' => 'nullable|numeric|min:0',
            'lot_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($implant, $validated) {
            $newQuantity = $implant->stock_quantity + $validated['quantity'];

            $updateData = ['stock_quantity' => $newQuantity];
            if (! empty($validated['purchase_price'])) {
                $updateData['purchase_price'] = $validated['purchase_price'];
            }
            if (! empty($validated['lot_number'])) {
                $updateData['lot_number'] = $validated['lot_number'];
            }

            $implant->update($updateData);

            ImplantStockTransaction::create([
                'implant_id' => $implant->id,
                'type' => 'purchase_restock',
                'quantity' => $validated['quantity'],
                'balance_after' => $newQuantity,
                'notes' => $validated['notes'] ?? 'Stock replenished',
                'created_by' => auth()->id(),
            ]);
        });

        return back()->with('success', "Added {$validated['quantity']} units to {$implant->name} stock successfully.");
    }

    public function destroy(Implant $implant)
    {
        if ($implant->saleItems()->exists()) {
            return back()->with('error', 'Cannot delete implant with recorded sales history. You can mark it as Inactive instead.');
        }

        $implant->delete();

        return redirect()->route('implants.index')->with('success', 'Implant deleted successfully.');
    }
}
