@extends('layouts.app')

@section('title', 'Stock Ledger')

@section('content')

    @if ($lowStockCount > 0)
        <div class="alert alert-warning d-flex justify-content-between align-items-center py-2">
            <span>
                <i class="bi bi-exclamation-triangle me-1"></i>
                <strong>{{ $lowStockCount }}</strong> product(s) are at or below their minimum stock level.
            </span>
            <a href="{{ route('medicines.index', ['low_stock' => 1]) }}" class="btn btn-sm btn-outline-dark">
                View products
            </a>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0">Stock Ledger</h5>
            <div class="d-flex gap-2">
                <a href="{{ route('stock.create', ['type' => 'in']) }}" class="btn btn-success btn-sm">
                    <i class="bi bi-box-arrow-in-down me-1"></i> Stock In
                </a>
                <a href="{{ route('stock.create', ['type' => 'out']) }}" class="btn btn-danger btn-sm">
                    <i class="bi bi-box-arrow-up me-1"></i> Stock Out
                </a>
            </div>
        </div>

        <div class="card-body">

            <form method="GET" action="{{ route('stock.index') }}" class="row g-2 mb-3">
                <div class="col-md-5">
                    <select name="medicine_id" class="form-select">
                        <option value="">All products</option>
                        @foreach ($medicines as $medicine)
                            <option value="{{ $medicine->id }}"
                                {{ request('medicine_id') == $medicine->id ? 'selected' : '' }}>
                                {{ $medicine->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="type" class="form-select">
                        <option value="">All types</option>
                        @foreach (\App\Models\StockTransaction::types() as $typeValue => $typeLabel)
                            <option value="{{ $typeValue }}" {{ request('type') === $typeValue ? 'selected' : '' }}>
                                {{ $typeLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-outline-secondary"><i class="bi bi-search me-1"></i>
                        Filter</button>
                    <a href="{{ route('stock.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Product</th>
                            <th>Type</th>
                            <th>Qty</th>
                            <th>Unit Cost</th>
                            <th>Total Cost</th>
                            <th>Supplier</th>
                            <th>Reference</th>
                            <th>By</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transactions as $transaction)
                            <tr>
                                <td>{{ $transaction->transaction_date->format('d M Y') }}</td>
                                <td>
                                    <a href="{{ route('medicines.show', $transaction->medicine) }}"
                                        class="text-decoration-none">
                                        {{ $transaction->medicine->name }}
                                    </a>
                                </td>
                                <td>
                                    <span class="badge text-bg-{{ $transaction->typeColor() }}">
                                        {{ $transaction->typeLabel() }}
                                    </span>
                                </td>
                                <td class="fw-semibold">{{ $transaction->quantity }}</td>
                                <td>{{ $transaction->unit_cost ? number_format($transaction->unit_cost, 2) : '—' }}</td>
                                <td>
                                    {{ $transaction->totalCost() !== null ? number_format($transaction->totalCost(), 2) : '—' }}
                                    @if ($transaction->expense_id)
                                        <span class="badge text-bg-success"
                                            title="Also posted as an expense">Expensed</span>
                                    @endif
                                </td>
                                </td>
                                <td>{{ $transaction->supplier->name ?? '—' }}</td>
                                <td>{{ $transaction->reference ?? '—' }}</td>
                                <td>{{ $transaction->createdBy->name ?? '—' }}</td>
                                <td class="text-end">
                                    <form method="POST" action="{{ route('stock.destroy', $transaction) }}"
                                        class="d-inline"
                                        onsubmit="return confirm('Delete this transaction? The product stock will be adjusted back.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                            title="Delete (reverses the stock change)"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">No stock transactions yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $transactions->links() }}

        </div>
    </div>

@endsection
