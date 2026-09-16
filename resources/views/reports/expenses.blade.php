@extends('layouts.app')

@section('title', 'Expense Report')

@section('content')

    <div class="card shadow-sm mb-3 border-start border-danger border-4">
        <div class="card-body d-flex justify-content-between align-items-center py-3">
            <div>
                <div class="text-muted small">Total Spent @if (request()->filled('from') || request()->filled('to') || request()->filled('category_id'))
                        (for current filter)
                    @endif
                </div>
                <div class="fs-3 fw-bold text-danger">{{ number_format($filteredTotal, 2) }}</div>
            </div>
            <i class="bi bi-wallet2 fs-1 text-danger opacity-25"></i>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0">Expense Report</h5>
            <button type="button" class="btn btn-outline-dark btn-sm no-print" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print
            </button>
        </div>

        <div class="card-body">

            <form method="GET" action="{{ route('reports.expenses') }}" class="row g-2 mb-3 no-print">
                <div class="col-md-3">
                    <input type="date" name="from" value="{{ request('from') }}" class="form-control"
                        title="From date">
                </div>
                <div class="col-md-3">
                    <input type="date" name="to" value="{{ request('to') }}" class="form-control" title="To date">
                </div>
                <div class="col-md-3">
                    <select name="category_id" class="form-select">
                        <option value="">All categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}"
                                {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-outline-secondary"><i class="bi bi-search me-1"></i>
                        Filter</button>
                    <a href="{{ route('reports.expenses') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Method</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($expenses as $expense)
                            <tr>
                                <td>{{ $expense->expense_date->format('d M Y') }}</td>
                                <td><span class="badge text-bg-light text-dark">{{ $expense->expenseCategory->name }}</span>
                                </td>
                                <td>
                                    {{ $expense->title }}
                                    @if ($expense->description)
                                        <div class="text-muted small">
                                            {{ \Illuminate\Support\Str::limit($expense->description, 60) }}</div>
                                    @endif
                                </td>
                                <td class="fw-semibold text-danger">{{ number_format($expense->amount, 2) }}</td>
                                <td>{{ $expense->methodLabel() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No expenses found for this filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $expenses->links() }}

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
