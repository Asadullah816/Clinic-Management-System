@extends('layouts.app')

@section('title', 'Product Details')

@section('content')

    {{-- Stock status alert --}}
    @if ($medicine->isExpired())
        <div class="alert alert-danger"><i class="bi bi-exclamation-octagon me-1"></i>
            This product is <strong>expired</strong> ({{ $medicine->expiry_date->format('d M Y') }}). It should not be used
            on patients.</div>
    @elseif ($medicine->isOutOfStock())
        <div class="alert alert-danger"><i class="bi bi-x-circle me-1"></i> This product is <strong>out of stock</strong>.
        </div>
    @elseif ($medicine->isLowStock())
        <div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-1"></i>
            Low stock: only <strong>{{ $medicine->stock_quantity }} {{ $medicine->unit }}</strong> left (minimum:
            {{ $medicine->minimum_stock }}). Consider restocking.</div>
    @endif

    <div class="card shadow-sm" style="max-width: 860px;">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ $medicine->name }}</h5>
            <div>
                <span class="badge {{ $medicine->status === 'active' ? 'text-bg-success' : 'text-bg-secondary' }}">
                    {{ ucfirst($medicine->status) }}
                </span>
                <span class="badge text-bg-info">{{ $medicine->category->name }}</span>
            </div>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr>
                            <th class="w-50">Generic Name</th>
                            <td>{{ $medicine->generic_name ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>Supplier</th>
                            <td>{{ $medicine->supplier->name ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>Unit</th>
                            <td>{{ $medicine->unit ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>Current Stock</th>
                            <td class="fw-semibold">{{ $medicine->stock_quantity }} {{ $medicine->unit }}</td>
                        </tr>
                        <tr>
                            <th>Minimum Stock</th>
                            <td>{{ $medicine->minimum_stock }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr>
                            <th class="w-50">Purchase Price</th>
                            <td>{{ number_format($medicine->purchase_price, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Selling Price</th>
                            <td>{{ number_format($medicine->selling_price, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Expiry Date</th>
                            <td>{{ $medicine->expiry_date?->format('d M Y') ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>Description</th>
                            <td>{{ $medicine->description ?? '—' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- Placeholder sections — populated in Phases 10 & 11 --}}
            <div class="col-md-6">
                <div class="card bg-light border-0 h-100" id="usage-history">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">Usage History</h6>
                            <a href="{{ route('medicine-usages.create', ['medicine_id' => $medicine->id, 'redirect' => 'medicine']) }}"
                                class="btn btn-sm btn-outline-primary" title="Record usage">
                                <i class="bi bi-plus-lg"></i>
                            </a>
                        </div>

                        @if ($medicine->medicineUsages->isEmpty())
                            <p class="text-muted text-center py-3 mb-0">No usage records yet.</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Patient</th>
                                            <th>Treatment</th>
                                            <th>Qty</th>
                                            <th>By</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($medicine->medicineUsages as $usage)
                                            <tr>
                                                <td>{{ $usage->usage_date->format('d M Y') }}</td>
                                                <td>{{ $usage->patient->full_name }}</td>
                                                <td>{{ $usage->treatment->name ?? '—' }}</td>
                                                <td class="fw-semibold">{{ $usage->quantity }}</td>
                                                <td>{{ $usage->usedBy->name ?? '—' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <a href="{{ route('medicine-usages.index', ['medicine_id' => $medicine->id]) }}"
                                class="small mt-2 d-block">
                                View full usage log &rarr;
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 mt-3">
                <a href="{{ route('medicines.edit', $medicine) }}" class="btn btn-primary">
                    <i class="bi bi-pencil me-1"></i> Edit
                </a>
                <form method="POST" action="{{ route('medicines.destroy', $medicine) }}"
                    onsubmit="return confirm('Delete product &quot;{{ $medicine->name }}&quot;?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="bi bi-trash me-1"></i> Delete
                    </button>
                </form>
                <a href="{{ route('medicines.index') }}" class="btn btn-outline-secondary ms-auto">
                    <i class="bi bi-arrow-left me-1"></i> Back to Products
                </a>
            </div>
        </div>
    </div>

@endsection
