@extends('layouts.app')

@section('title', 'Laser Expenses')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="mb-0 fw-bold" style="color: #0f172a;">Laser Expenses &amp; Maintenance</h4>
            <p class="text-muted small mb-0">Separate expenditure ledger for laser cartridges, maintenance, consumables, and operations.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('laser.expense-categories.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1 shadow-sm">
                <i class="bi bi-tags"></i> Categories
            </a>
            <a href="{{ route('laser.expenses.create') }}" class="btn btn-danger d-inline-flex align-items-center gap-1 shadow-sm">
                <i class="bi bi-dash-circle"></i> Add Laser Expense
            </a>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('laser.expenses.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0"
                               placeholder="Search title, reference..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <select name="category_id" class="form-select form-select-sm">
                        <option value="">All Categories</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}" placeholder="From date">
                </div>
                <div class="col-6 col-md-2">
                    <input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}" placeholder="To date">
                </div>
                <div class="col-6 col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-dark w-100">Filter</button>
                    @if (request()->hasAny(['search', 'category_id', 'from', 'to']))
                        <a href="{{ route('laser.expenses.index') }}" class="btn btn-sm btn-outline-secondary" title="Reset">
                            <i class="bi bi-x"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Total Banner --}}
    <div class="alert alert-light border shadow-sm d-flex justify-content-between align-items-center mb-4">
        <span class="text-muted fw-semibold">Filtered Laser Expenses:</span>
        <span class="fw-bold fs-5 text-danger">PKR {{ number_format($totalFiltered, 2) }}</span>
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
                            <th>Paid Method</th>
                            <th>Amount (PKR)</th>
                            <th>Recorded By</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($expenses as $expense)
                            <tr>
                                <td class="ps-3">{{ $expense->expense_date->format('d M Y') }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $expense->title }}</div>
                                    @if ($expense->reference)
                                        <div class="text-muted small">Ref: {{ $expense->reference }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $expense->expenseCategory->name ?? 'General' }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary">{{ $expense->methodLabel() }}</span>
                                </td>
                                <td class="fw-bold text-danger">
                                    PKR {{ number_format($expense->amount, 2) }}
                                </td>
                                <td class="small text-muted">{{ $expense->createdBy->name ?? 'Staff' }}</td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('laser.expenses.edit', $expense) }}" class="btn btn-outline-primary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form method="POST" action="{{ route('laser.expenses.destroy', $expense) }}"
                                              class="d-inline" onsubmit="return confirm('Delete this laser expense?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-wallet2 fs-1 d-block mb-2 text-secondary"></i>
                                    No laser expenses recorded.
                                    <div class="mt-2">
                                        <a href="{{ route('laser.expenses.create') }}" class="btn btn-sm btn-danger">
                                            <i class="bi bi-dash-circle me-1"></i> Add Laser Expense
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($expenses->hasPages())
            <div class="card-footer bg-white py-3">
                {{ $expenses->links() }}
            </div>
        @endif
    </div>
@endsection
