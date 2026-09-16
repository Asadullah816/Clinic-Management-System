@extends('layouts.app')

@section('title', 'Medicines & Products')

@section('content')

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0">Medicines &amp; Products</h5>
            <a href="{{ route('medicines.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Add Product
            </a>
        </div>

        <div class="card-body">

            <form method="GET" action="{{ route('medicines.index') }}" class="row g-2 mb-1">
                <div class="col-md-4">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                        placeholder="Search name or generic name...">
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
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-outline-secondary"><i class="bi bi-search me-1"></i>
                        Filter</button>
                    <a href="{{ route('medicines.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>

            <div class="row g-2 mb-3">
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="low_stock" value="1" id="low_stock"
                            {{ request('low_stock') ? 'checked' : '' }}>
                        <label class="form-check-label" for="low_stock">Low stock only</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="expired" value="1" id="expired"
                            {{ request('expired') ? 'checked' : '' }}>
                        <label class="form-check-label" for="expired">Expired only</label>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Supplier</th>
                            <th>Selling Price</th>
                            <th>Stock</th>
                            <th>Expiry</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($medicines as $medicine)
                            <tr>
                                <td>
                                    <a href="{{ route('medicines.show', $medicine) }}"
                                        class="text-decoration-none fw-semibold">
                                        {{ $medicine->name }}
                                    </a>
                                    @if ($medicine->generic_name)
                                        <div class="text-muted small">{{ $medicine->generic_name }}</div>
                                    @endif
                                </td>
                                <td>{{ $medicine->category->name }}</td>
                                <td>{{ $medicine->supplier->name ?? '—' }}</td>
                                <td>{{ number_format($medicine->selling_price, 2) }}</td>
                                <td>
                                    @if ($medicine->isOutOfStock())
                                        <span class="badge text-bg-danger">Out of stock</span>
                                    @elseif ($medicine->isLowStock())
                                        <span class="badge text-bg-warning text-dark">Low:
                                            {{ $medicine->stock_quantity }}</span>
                                    @else
                                        <span class="fw-semibold">{{ $medicine->stock_quantity }}</span>
                                        <span class="text-muted small">{{ $medicine->unit }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($medicine->expiry_date)
                                        @if ($medicine->isExpired())
                                            <span
                                                class="badge text-bg-danger">{{ $medicine->expiry_date->format('d M Y') }}
                                                — Expired</span>
                                        @elseif ($medicine->isExpiringSoon())
                                            <span
                                                class="badge text-bg-warning text-dark">{{ $medicine->expiry_date->format('d M Y') }}</span>
                                        @else
                                            {{ $medicine->expiry_date->format('d M Y') }}
                                        @endif
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    <span
                                        class="badge {{ $medicine->status === 'active' ? 'text-bg-success' : 'text-bg-secondary' }}">
                                        {{ ucfirst($medicine->status) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('medicines.show', $medicine) }}"
                                        class="btn btn-sm btn-outline-secondary" title="View"><i
                                            class="bi bi-eye"></i></a>
                                    <a href="{{ route('medicines.edit', $medicine) }}"
                                        class="btn btn-sm btn-outline-primary" title="Edit"><i
                                            class="bi bi-pencil"></i></a>
                                    <form method="POST" action="{{ route('medicines.destroy', $medicine) }}"
                                        class="d-inline"
                                        onsubmit="return confirm('Delete product &quot;{{ $medicine->name }}&quot;? Historical records keep its name.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i
                                                class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">No products found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $medicines->links() }}

        </div>
    </div>

@endsection
