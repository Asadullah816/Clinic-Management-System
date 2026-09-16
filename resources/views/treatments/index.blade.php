@extends('layouts.app')

@section('title', 'Treatments')

@section('content')

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0">Treatments &amp; Services</h5>
            <a href="{{ route('treatments.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Add Treatment
            </a>
        </div>

        <div class="card-body">

            <form method="GET" action="{{ route('treatments.index') }}" class="row g-2 mb-3">
                <div class="col-md-6">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                        placeholder="Search by name or description...">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-outline-secondary"><i class="bi bi-search me-1"></i>
                        Filter</button>
                    <a href="{{ route('treatments.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Price</th>
                            <th>Duration</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($treatments as $treatment)
                            <tr>
                                <td class="fw-semibold">{{ $treatment->name }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($treatment->description ?? '—', 50) }}</td>
                                <td>{{ number_format($treatment->price, 2) }}</td>
                                <td>{{ $treatment->duration ? $treatment->duration . ' min' : '—' }}</td>
                                <td>
                                    <span
                                        class="badge {{ $treatment->status === 'active' ? 'text-bg-success' : 'text-bg-secondary' }}">
                                        {{ ucfirst($treatment->status) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('treatments.edit', $treatment) }}"
                                        class="btn btn-sm btn-outline-primary" title="Edit"><i
                                            class="bi bi-pencil"></i></a>
                                    <form method="POST" action="{{ route('treatments.destroy', $treatment) }}"
                                        class="d-inline"
                                        onsubmit="return confirm('Delete treatment &quot;{{ $treatment->name }}&quot;? Historical records keep its name.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i
                                                class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No treatments found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $treatments->links() }}

        </div>
    </div>

@endsection
