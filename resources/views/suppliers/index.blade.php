@extends('layouts.app')

@section('title', 'Suppliers')

@section('content')

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0">Suppliers</h5>
            <a href="{{ route('suppliers.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Add Supplier
            </a>
        </div>

        <div class="card-body">

            <form method="GET" action="{{ route('suppliers.index') }}" class="row g-2 mb-3">
                <div class="col-md-5">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                        placeholder="Search by name, company, or phone...">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-outline-secondary"><i class="bi bi-search me-1"></i>
                        Filter</button>
                    <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Company</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Products</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($suppliers as $supplier)
                            <tr>
                                <td class="fw-semibold">{{ $supplier->name }}</td>
                                <td>{{ $supplier->company ?? '—' }}</td>
                                <td>{{ $supplier->phone ?? '—' }}</td>
                                <td>{{ $supplier->email ?? '—' }}</td>
                                <td><span class="badge text-bg-light text-dark">{{ $supplier->medicines_count }}</span></td>
                                <td>
                                    <span
                                        class="badge {{ $supplier->status === 'active' ? 'text-bg-success' : 'text-bg-secondary' }}">
                                        {{ ucfirst($supplier->status) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('suppliers.edit', $supplier) }}"
                                        class="btn btn-sm btn-outline-primary" title="Edit"><i
                                            class="bi bi-pencil"></i></a>
                                    <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}"
                                        class="d-inline"
                                        onsubmit="return confirm('Delete supplier &quot;{{ $supplier->name }}&quot;?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i
                                                class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No suppliers found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $suppliers->links() }}

        </div>
    </div>

@endsection
