@extends('layouts.app')

@section('title', 'Laser Expense Categories')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="mb-0 fw-bold" style="color: #0f172a;">Laser Expense Categories</h4>
            <p class="text-muted small mb-0">Cost center categories for tracking laser shared assets, maintenance, and supplies.</p>
        </div>
        <a href="{{ route('laser.expenses.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Expenses
        </a>
    </div>

    <div class="row g-4">
        {{-- Add Category Form --}}
        <div class="col-12 col-md-5">
            <div class="card border shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-plus-circle me-1 text-primary"></i> Add New Category</h6>
                </div>
                <div class="card-body p-3">
                    <form method="POST" action="{{ route('laser.expense-categories.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold">Category Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                                   placeholder="e.g. Optics & Handpiece Servicing" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label fw-semibold">Description</label>
                            <textarea name="description" id="description" rows="3"
                                      class="form-control @error('description') is-invalid @enderror"
                                      placeholder="Brief explanation of costs categorized here...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary w-100 fw-semibold">
                            <i class="bi bi-check-lg me-1"></i> Save Category
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Categories List --}}
        <div class="col-12 col-md-7">
            <div class="card border shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-tags me-1 text-primary"></i> Registered Categories</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small text-uppercase text-secondary">
                                <tr>
                                    <th class="ps-3">Category</th>
                                    <th>Expenses</th>
                                    <th class="text-end pe-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($categories as $category)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="fw-bold text-dark">{{ $category->name }}</div>
                                            @if ($category->description)
                                                <div class="text-muted small">{{ $category->description }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary-subtle text-secondary">
                                                {{ $category->expenses_count }} expense{{ $category->expenses_count === 1 ? '' : 's' }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-3">
                                            <form method="POST" action="{{ route('laser.expense-categories.destroy', $category) }}"
                                                  onsubmit="return confirm('Delete category {{ $category->name }}?');" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"
                                                        {{ $category->expenses_count > 0 ? 'disabled' : '' }}>
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted">
                                            No laser expense categories created yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if ($categories->hasPages())
                    <div class="card-footer bg-white py-2">
                        {{ $categories->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
