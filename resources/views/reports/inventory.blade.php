@extends('layouts.app')

@section('title', 'Inventory Report')

@section('content')

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0">Inventory Report</h5>
            <button type="button" class="btn btn-outline-dark btn-sm no-print" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print
            </button>
        </div>

        <div class="card-body">

            <form method="GET" action="{{ route('reports.inventory') }}" class="row g-2 mb-3 no-print">
                <div class="col-md-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="low_stock" value="1" id="low_stock"
                            {{ request('low_stock') ? 'checked' : '' }}>
                        <label class="form-check-label" for="low_stock">Low stock only</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="expired" value="1" id="expired"
                            {{ request('expired') ? 'checked' : '' }}>
                        <label class="form-check-label" for="expired">Expired only</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-outline-secondary"><i class="bi bi-search me-1"></i>
                        Filter</button>
                    <a href="{{ route('reports.inventory') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Current Stock</th>
                            <th>Minimum Stock</th>
                            <th>Expiry Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($medicines as $medicine)
                            <tr>
                                <td class="fw-semibold">{{ $medicine->name }}</td>
                                <td>{{ $medicine->category->name }}</td>
                                <td>
                                    @if ($medicine->isOutOfStock())
                                        <span class="badge text-bg-danger">Out of stock</span>
                                    @elseif ($medicine->isLowStock())
                                        <span class="badge text-bg-warning text-dark">Low:
                                            {{ $medicine->stock_quantity }}</span>
                                    @else
                                        {{ $medicine->stock_quantity }} {{ $medicine->unit }}
                                    @endif
                                </td>
                                <td>{{ $medicine->minimum_stock }}</td>
                                <td>
                                    @if ($medicine->expiry_date)
                                        @if ($medicine->isExpired())
                                            <span
                                                class="badge text-bg-danger">{{ $medicine->expiry_date->format('d M Y') }}
                                                — Expired</span>
                                        @else
                                            {{ $medicine->expiry_date->format('d M Y') }}
                                        @endif
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No products found for this filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $medicines->links() }}

        </div>
    </div>

    <style>
        @media print {

            .sidebar,
            .navbar,
            footer,
            .no-print {
                display: none !important;
            }

            .main-wrapper {
                margin-left: 0 !important;
            }

            body {
                background: #fff !important;
            }

            .card {
                border: none !important;
                box-shadow: none !important;
            }
        }
    </style>

@endsection
