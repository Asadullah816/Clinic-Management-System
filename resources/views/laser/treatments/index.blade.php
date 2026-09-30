@extends('layouts.app')

@section('title', 'Laser Services Catalog')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="mb-0 fw-bold" style="color: #0f172a;">Laser Treatments Catalog</h4>
            <p class="text-muted small mb-0">Distinct laser procedures, body areas, standard session pricing, and default durations.</p>
        </div>
        <a href="{{ route('laser.treatments.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-plus-circle"></i> Add Laser Treatment
        </a>
    </div>

    {{-- Treatments List --}}
    <div class="card border shadow-sm rounded-3">
        <div class="card-header bg-white py-3 border-bottom">
            <form method="GET" action="{{ route('laser.treatments.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0"
                               placeholder="Search procedure name, code, or body area..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-6 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-dark w-100">Filter</button>
                    @if (request()->hasAny(['search', 'status']))
                        <a href="{{ route('laser.treatments.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                    @endif
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-uppercase text-secondary">
                        <tr>
                            <th class="ps-3">Procedure Name</th>
                            <th>Body Area</th>
                            <th>Code</th>
                            <th>Duration</th>
                            <th>Price (PKR)</th>
                            <th>Status</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($treatments as $treatment)
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-bold text-dark">{{ $treatment->name }}</div>
                                    @if ($treatment->description)
                                        <div class="text-muted small text-truncate" style="max-width: 320px;">
                                            {{ $treatment->description }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $treatment->body_area ?? 'General' }}</span>
                                </td>
                                <td>
                                    <code>{{ $treatment->code ?? '—' }}</code>
                                </td>
                                <td>{{ $treatment->duration ? $treatment->duration . ' mins' : '—' }}</td>
                                <td class="fw-bold text-dark">
                                    PKR {{ number_format($treatment->price, 2) }}
                                </td>
                                <td>
                                    <span class="badge bg-{{ $treatment->statusColor() }}-subtle text-{{ $treatment->statusColor() }} border border-{{ $treatment->statusColor() }}-subtle">
                                        {{ ucfirst($treatment->status) }}
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('laser.treatments.edit', $treatment) }}" class="btn btn-outline-primary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form method="POST" action="{{ route('laser.treatments.destroy', $treatment) }}"
                                              class="d-inline" onsubmit="return confirm('Remove this laser treatment from catalog?');">
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
                                    <i class="bi bi-card-checklist fs-1 d-block mb-2 text-secondary"></i>
                                    No laser treatments registered in the catalog yet.
                                    <div class="mt-2">
                                        <a href="{{ route('laser.treatments.create') }}" class="btn btn-sm btn-primary">
                                            <i class="bi bi-plus-circle me-1"></i> Add First Laser Treatment
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($treatments->hasPages())
            <div class="card-footer bg-white py-3">
                {{ $treatments->links() }}
            </div>
        @endif
    </div>
@endsection
