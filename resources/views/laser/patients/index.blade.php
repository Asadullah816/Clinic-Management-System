@extends('layouts.app')

@section('title', 'Laser Patients')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="mb-0 fw-bold" style="color: #0f172a;">Laser Patient Registry</h4>
            <p class="text-muted small mb-0">Separate registry for laser procedure clients with Fitzpatrick skin typing and treatment history.</p>
        </div>
        <a href="{{ route('laser.patients.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-person-plus"></i> New Laser Patient
        </a>
    </div>

    {{-- Filter & Search Bar --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('laser.patients.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0"
                               placeholder="Search by name, patient #, or phone..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <select name="skin_type" class="form-select form-select-sm">
                        <option value="">All Skin Types</option>
                        @foreach (\App\Models\LaserPatient::fitzpatrickTypes() as $key => $label)
                            <option value="{{ $key }}" {{ request('skin_type') === $key ? 'selected' : '' }}>
                                {{ $key }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-dark w-100">Filter</button>
                    @if (request()->hasAny(['search', 'skin_type', 'status']))
                        <a href="{{ route('laser.patients.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Patients Table --}}
    <div class="card border shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-uppercase text-secondary">
                        <tr>
                            <th class="ps-3">Patient</th>
                            <th>Contact</th>
                            <th>Skin Type</th>
                            <th>Total Sessions</th>
                            <th>Status</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($patients as $patient)
                            <tr>
                                <td class="ps-3">
                                    <a href="{{ route('laser.patients.show', $patient) }}" class="fw-bold text-dark text-decoration-none">
                                        {{ $patient->full_name }}
                                    </a>
                                    <div class="text-muted small">
                                        <span class="badge bg-secondary-subtle text-secondary me-1">{{ $patient->patient_number }}</span>
                                        {{ ucfirst($patient->gender) }} {{ $patient->age ? '• ' . $patient->age . ' yrs' : '' }}
                                    </div>
                                </td>
                                <td>
                                    <div><i class="bi bi-telephone text-muted me-1 small"></i>{{ $patient->phone }}</div>
                                    @if ($patient->email)
                                        <div class="text-muted small"><i class="bi bi-envelope text-muted me-1 small"></i>{{ $patient->email }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if ($patient->skin_type)
                                        <span class="badge bg-light text-dark border">{{ $patient->skin_type }}</span>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-6">
                                        {{ $patient->sessions_count }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $patient->statusColor() }}-subtle text-{{ $patient->statusColor() }} border border-{{ $patient->statusColor() }}-subtle">
                                        {{ ucfirst($patient->status) }}
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('laser.sessions.create', ['patient_id' => $patient->id]) }}"
                                           class="btn btn-outline-success" title="New Laser Session">
                                            <i class="bi bi-plus-lg"></i> Session
                                        </a>
                                        <a href="{{ route('laser.patients.show', $patient) }}" class="btn btn-outline-primary" title="View Profile">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('laser.patients.edit', $patient) }}" class="btn btn-outline-secondary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-person-x fs-1 d-block mb-2 text-secondary"></i>
                                    No laser patients found.
                                    <div class="mt-2">
                                        <a href="{{ route('laser.patients.create') }}" class="btn btn-sm btn-primary">
                                            <i class="bi bi-person-plus me-1"></i> Register New Laser Patient
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($patients->hasPages())
            <div class="card-footer bg-white py-3">
                {{ $patients->links() }}
            </div>
        @endif
    </div>
@endsection
