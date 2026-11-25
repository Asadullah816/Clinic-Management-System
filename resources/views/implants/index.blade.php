@extends('layouts.app')

@section('title', 'Dental Implants Inventory')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h4 class="mb-0 fw-bold text-dark">
                <i class="bi bi-pin-angle text-primary me-2"></i>Dental Implants Inventory
            </h4>
            <p class="text-muted small mb-0">Manage implant fixtures, specifications, stock on hand, and supplies.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('failed-implants.index') }}" class="btn btn-outline-danger">
                <i class="bi bi-x-octagon me-1"></i> Failed Implants
            </a>
            <a href="{{ route('implant-sales.create') }}" class="btn btn-outline-primary">
                <i class="bi bi-cart-plus me-1"></i> New Implant Sale
            </a>
            <a href="{{ route('implants.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Add New Implant
            </a>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 border-start border-primary border-4 h-100">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold">Implant Models</span>
                    <h3 class="fw-bold mb-0 text-dark">{{ $stats['total_models'] }}</h3>
                    <span class="small text-muted">{{ $brands->count() }} brands registered</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 border-start border-success border-4 h-100">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold">Total Stock Units</span>
                    <h3 class="fw-bold mb-0 text-success">{{ number_format($stats['total_stock']) }}</h3>
                    <span class="small text-muted">Available fixtures</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 border-start border-warning border-4 h-100">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold">Low Stock Alert</span>
                    <h3 class="fw-bold mb-0 {{ $stats['low_stock'] > 0 ? 'text-warning-emphasis' : 'text-muted' }}">
                        {{ $stats['low_stock'] }}
                    </h3>
                    <span class="small text-muted">Below threshold</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 border-start border-danger border-4 h-100">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold">Out of Stock</span>
                    <h3 class="fw-bold mb-0 {{ $stats['out_of_stock'] > 0 ? 'text-danger' : 'text-muted' }}">
                        {{ $stats['out_of_stock'] }}
                    </h3>
                    <span class="small text-muted">Needs replenishment</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Separate Failed Implants Overview Alert --}}
    @if (($stats['failed_units'] ?? 0) > 0)
        <div class="alert alert-danger-subtle border border-danger-subtle d-flex flex-wrap justify-content-between align-items-center p-3 mb-4 rounded-3 shadow-sm gap-2">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-x-octagon-fill text-danger fs-4"></i>
                <div>
                    <strong class="text-danger">Failed Implants Tracked Separately:</strong>
                    <span class="text-dark">{{ $stats['failed_units'] }} unit(s) recorded with total loss valuation of <strong>PKR {{ number_format($stats['failed_cost'], 2) }}</strong>.</span>
                    <span class="text-muted small d-block">These losses are calculated in an isolated ledger and do not reduce active sales revenue or regular stock.</span>
                </div>
            </div>
            <a href="{{ route('failed-implants.index') }}" class="btn btn-sm btn-danger text-nowrap">
                <i class="bi bi-eye me-1"></i> View Failed Implants
            </a>
        </div>
    @endif

    {{-- Filter & Search --}}
    <div class="card shadow-sm mb-3">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('implants.index') }}" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control"
                            placeholder="Search by name, code, brand, diameter, length..."
                            value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="brand" class="form-select form-select-sm">
                        <option value="">-- All Brands --</option>
                        @foreach ($brands as $b)
                            <option value="{{ $b }}" {{ request('brand') === $b ? 'selected' : '' }}>{{ $b }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="stock_status" class="form-select form-select-sm">
                        <option value="">-- All Stock Status --</option>
                        <option value="in_stock" {{ request('stock_status') === 'in_stock' ? 'selected' : '' }}>In Stock Only</option>
                        <option value="low_stock" {{ request('stock_status') === 'low_stock' ? 'selected' : '' }}>Low Stock Alerts</option>
                        <option value="out_of_stock" {{ request('stock_status') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock Only</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-primary flex-grow-1">Filter</button>
                    @if(request('search') || request('brand') || request('stock_status'))
                        <a href="{{ route('implants.index') }}" class="btn btn-sm btn-outline-secondary" title="Clear Filters">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Implants Table --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Code</th>
                            <th>Implant Product</th>
                            <th>Brand</th>
                            <th>Specs (Ø × L)</th>
                            <th>Platform / Connection</th>
                            <th>Stock</th>
                            <th>Sale Price</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($implants as $implant)
                            <tr>
                                <td class="ps-3">
                                    <span class="badge bg-light text-dark border fw-bold">{{ $implant->item_code }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('implants.show', $implant) }}" class="fw-bold text-dark text-decoration-none">
                                        {{ $implant->name }}
                                    </a>
                                    @if ($implant->material)
                                        <div class="small text-muted">{{ $implant->material }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if ($implant->brand)
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $implant->brand }}</span>
                                    @else
                                        <span class="text-muted small">&mdash;</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="fw-semibold text-dark">
                                        {{ $implant->diameter ? 'Ø '.$implant->diameter : '' }}
                                        {{ $implant->diameter && $implant->length ? ' × ' : '' }}
                                        {{ $implant->length ? 'L: '.$implant->length : '' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="small text-muted">{{ $implant->platform ?: ($implant->connection_type ?: '—') }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $implant->stockStatusColor() }}-subtle text-{{ $implant->stockStatusColor() }} border border-{{ $implant->stockStatusColor() }}-subtle px-2 py-1">
                                        <i class="bi bi-box-seam me-1"></i>{{ $implant->stockStatusLabel() }}
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">PKR {{ number_format($implant->sale_price, 2) }}</div>
                                    <div class="small text-muted">Cost: PKR {{ number_format($implant->purchase_price, 2) }}</div>
                                </td>
                                <td class="text-end pe-3">
                                    {{-- Quick Restock Button --}}
                                    <button type="button" class="btn btn-sm btn-outline-success me-1"
                                        data-bs-toggle="modal" data-bs-target="#restockModal{{ $implant->id }}"
                                        title="Replenish Stock">
                                        <i class="bi bi-box-arrow-in-down"></i>
                                    </button>
                                    <a href="{{ route('implants.show', $implant) }}" class="btn btn-sm btn-outline-secondary me-1" title="View details">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('implants.edit', $implant) }}" class="btn btn-sm btn-outline-primary me-1" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" action="{{ route('implants.destroy', $implant) }}" class="d-inline"
                                        onsubmit="return confirm('Delete implant {{ $implant->name }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>

                                    {{-- Restock Modal --}}
                                    <div class="modal fade text-start" id="restockModal{{ $implant->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <form method="POST" action="{{ route('implants.restock', $implant) }}" class="modal-content">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold">Restock {{ $implant->name }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p class="small text-muted mb-3">
                                                        Current stock on hand: <strong class="text-dark">{{ $implant->stock_quantity }} units</strong>.
                                                    </p>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Quantity to Add <span class="text-danger">*</span></label>
                                                        <input type="number" name="quantity" min="1" class="form-control" required placeholder="e.g. 5, 10, 20">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">New Purchase / Cost Price (PKR)</label>
                                                        <input type="number" step="0.01" name="purchase_price" value="{{ $implant->purchase_price }}" class="form-control">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Lot / Batch Number</label>
                                                        <input type="text" name="lot_number" value="{{ $implant->lot_number }}" class="form-control" placeholder="e.g. LOT-2026-X8">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Notes</label>
                                                        <textarea name="notes" class="form-control" rows="2" placeholder="Supplier or invoice ref..."></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-success">
                                                        <i class="bi bi-check-lg me-1"></i> Add Stock
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-pin-angle fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                    No dental implants found in inventory.
                                    <div class="mt-2">
                                        <a href="{{ route('implants.create') }}" class="btn btn-sm btn-primary">
                                            + Add First Dental Implant
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($implants->hasPages())
                <div class="p-3 border-top">
                    {{ $implants->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
