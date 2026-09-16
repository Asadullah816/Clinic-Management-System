@extends('layouts.app')

@section('title', 'Medicine Categories')

@section('content')

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0">Medicine Categories</h5>
            <a href="{{ route('medicine-categories.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Add Category
            </a>
        </div>

        <div class="card-body">

            <form method="GET" action="{{ route('medicine-categories.index') }}" class="row g-2 mb-3">
                <div class="col-md-5">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                        placeholder="Search categories...">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-outline-secondary"><i class="bi bi-search me-1"></i>
                        Filter</button>
                    <a href="{{ route('medicine-categories.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Products</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($categories as $category)
                            <tr>
                                <td class="fw-semibold">{{ $category->name }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($category->description ?? '—', 60) }}</td>
                                <td><span class="badge text-bg-light text-dark">{{ $category->medicines_count }}</span></td>
                                <td>
                                    <span
                                        class="badge {{ $category->status === 'active' ? 'text-bg-success' : 'text-bg-secondary' }}">
                                        {{ ucfirst($category->status) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('medicine-categories.edit', $category) }}"
                                        class="btn btn-sm btn-outline-primary" title="Edit"><i
                                            class="bi bi-pencil"></i></a>
                                    <form method="POST" action="{{ route('medicine-categories.destroy', $category) }}"
                                        class="d-inline"
                                        onsubmit="return confirm('Delete category &quot;{{ $category->name }}&quot;?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i
                                                class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No categories found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $categories->links() }}

        </div>
    </div>

@endsection
