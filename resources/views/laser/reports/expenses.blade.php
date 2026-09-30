@extends('layouts.app')

@section('title', 'Laser Expenses Report')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3 no-print">
        <div>
            <h4 class="mb-0 fw-bold" style="color: #0f172a;">Laser Expenses Report</h4>
            <p class="text-muted small mb-0">Breakdown of maintenance, cartridges, and operational costs for the laser shared unit.</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-dark d-inline-flex align-items-center gap-1 shadow-sm" onclick="window.print()">
                <i class="bi bi-printer"></i> Print Report
            </button>
            <a href="{{ route('laser.dashboard') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Dashboard
            </a>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4 no-print">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('laser.reports.expenses') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-4">
                    <label class="form-label small text-muted mb-0">Category</label>
                    <select name="category_id" class="form-select form-select-sm">
                        <option value="">All Categories</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small text-muted mb-0">From</label>
                    <input type="date" name="from" class="form-control form-control-sm" value="{{ $from }}">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small text-muted mb-0">To</label>
                    <input type="date" name="to" class="form-control form-control-sm" value="{{ $to }}">
                </div>
                <div class="col-12 col-md-2 d-flex align-items-end gap-1">
                    <button type="submit" class="btn btn-sm btn-dark w-100">Filter</button>
                    @if (request()->hasAny(['category_id', 'from', 'to']))
                        <a href="{{ route('laser.reports.expenses') }}" class="btn btn-sm btn-outline-secondary" title="Reset">
                            <i class="bi bi-x"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Summary Card --}}
    <div class="alert alert-light border shadow-sm d-flex justify-content-between align-items-center mb-4">
        <span class="text-muted fw-semibold">Total Laser Expenses for Selected Period:</span>
        <span class="fw-bold fs-4 text-danger">PKR {{ number_format($totalAmount, 2) }}</span>
    </div>

    {{-- Expenses Table --}}
    <div class="card border shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-uppercase text-secondary">
                        <tr>
                            <th class="ps-3">Date</th>
                            <th>Expense Title</th>
                            <th>Category</th>
                            <th>Method</th>
                            <th>Reference</th>
                            <th>Amount (PKR)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($expenses as $expense)
                            <tr>
                                <td class="ps-3">{{ $expense->expense_date->format('d M Y') }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $expense->title }}</div>
                                    @if ($expense->description)
                                        <div class="text-muted small text-truncate" style="max-width: 300px;">{{ $expense->description }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $expense->expenseCategory->name ?? 'General' }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary">{{ $expense->methodLabel() }}</span>
                                </td>
                                <td>{{ $expense->reference ?? '—' }}</td>
                                <td class="fw-bold text-danger">PKR {{ number_format($expense->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No laser expenses found for this period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($expenses->hasPages())
            <div class="card-footer bg-white py-3 no-print">
                {{ $expenses->links() }}
            </div>
        @endif
    </div>

    <style>
        @media print {
            .no-print, .sidebar, .app-navbar, .offcanvas {
                display: none !important;
            }
        }
    </style>
@endsection
