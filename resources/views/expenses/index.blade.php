@extends('layouts.app')

@section('title', 'Expenses')

@section('content')

    {{-- Filtered total summary card --}}
    <div class="card shadow-sm mb-3 border-start border-danger border-4">
        <div class="card-body d-flex justify-content-between align-items-center py-3">
            <div>
                <div class="text-muted small">
                    Total — {{ $periodLabel }}@if (request()->filled('category_id') || request()->filled('search'))
                        (also filtered)
                    @endif
                </div>
                <div class="fs-3 fw-bold text-danger">{{ number_format($filteredTotal, 2) }}</div>
            </div>
            <i class="bi bi-wallet2 fs-1 text-danger opacity-25"></i>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0">Expenses</h5>
            <a href="{{ route('expenses.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Record Expense
            </a>
        </div>

        <div class="card-body">

            <form method="GET" action="{{ route('expenses.index') }}" class="row g-2 mb-3">
                <div class="col-md-3">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                        placeholder="Search title or reference...">
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
                <form method="GET" action="{{ route('expenses.index') }}" class="row g-2 mb-3">
                    <div class="col-md-3">
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                            placeholder="Search title or reference...">
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
                        <select name="period" class="form-select" onchange="this.form.submit()">
                            @foreach ($periods as $value => $label)
                                <option value="{{ $value }}"
                                    {{ request('period', 'all') === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-outline-secondary"><i class="bi bi-search me-1"></i>
                            Filter</button>
                        <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Reference</th>
                            <th>By</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($expenses as $expense)
                            <tr>
                                <td>{{ $expense->expense_date->format('d M Y') }}</td>
                                <td class="fw-semibold">
                                    {{ $expense->title }}
                                    @if ($expense->description)
                                        <div class="text-muted small fw-normal">
                                            {{ \Illuminate\Support\Str::limit($expense->description, 60) }}</div>
                                    @endif
                                </td>
                                <td><span
                                        class="badge text-bg-light text-dark">{{ $expense->expenseCategory->name }}</span>
                                </td>
                                <td class="fw-semibold text-danger">{{ number_format($expense->amount, 2) }}</td>
                                <td>{{ $expense->methodLabel() }}</td>
                                <td>{{ $expense->reference ?? '—' }}</td>
                                <td>{{ $expense->createdBy->name ?? '—' }}</td>
                                <td class="text-end">
                                    <a href="{{ route('expenses.edit', $expense) }}" class="btn btn-sm btn-outline-primary"
                                        title="Edit"><i class="bi bi-pencil"></i></a>
                                    <form method="POST" action="{{ route('expenses.destroy', $expense) }}"
                                        class="d-inline"
                                        onsubmit="return confirm('Delete expense &quot;{{ $expense->title }}&quot;?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i
                                                class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">No expenses found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $expenses->links() }}

        </div>
    </div>

@endsection
