@extends('layouts.app')

@section('title', 'Patients')

@section('content')

    @php $canManage = auth()->user()->hasRole('admin', 'receptionist', 'staff'); @endphp

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <div>
                <h5 class="mb-0">{{ auth()->user()->isDoctor() ? 'My Patients' : 'All Patients' }}</h5>
                @if (auth()->user()->isDoctor())
                    <span class="text-muted small">Showing patients assigned to or treated by you</span>
                @endif
            </div>
            @if ($canManage)
                <a href="{{ route('patients.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i> Add Patient
                </a>
            @endif
        </div>

        <div class="card-body">

            {{-- Search & status filter --}}
            <form method="GET" action="{{ route('patients.index') }}" class="row g-2 mb-3">
                <div class="{{ auth()->user()->isDoctor() ? 'col-md-4' : 'col-md-3' }}">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                        placeholder="Search by patient #, name, or phone...">
                </div>
                @if (! auth()->user()->isDoctor() && isset($doctors))
                    <div class="col-md-2">
                        <select name="doctor_id" class="form-select">
                            <option value="">All Doctors</option>
                            @foreach ($doctors as $doc)
                                <option value="{{ $doc->id }}" {{ (string) request('doctor_id') === (string) $doc->id ? 'selected' : '' }}>
                                    {{ $doc->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="{{ auth()->user()->isDoctor() ? 'col-md-3' : 'col-md-2' }}">
                    <select name="period" class="form-select" onchange="this.form.submit()">
                        @foreach ($periods as $value => $label)
                            <option value="{{ $value }}" {{ request('period', 'all') === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="{{ auth()->user()->isDoctor() ? 'col-md-2' : 'col-md-3' }} d-flex gap-1">
                    <button type="submit" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-search me-1"></i> Filter
                    </button>
                    <a href="{{ route('patients.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Patient #</th>
                            <th>Name</th>
                            <th>Doctor</th>
                            <th>Gender</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Registered</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($patients as $patient)
                            <tr>
                                <td class="fw-semibold">{{ $patient->patient_number }}</td>
                                <td>
                                    <a href="{{ route('patients.show', $patient) }}" class="text-decoration-none">
                                        {{ $patient->full_name }}
                                    </a>
                                </td>
                                <td>
                                    @if ($patient->assignedDoctor)
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                            <i class="bi bi-person-badge me-1"></i> {{ $patient->assignedDoctor->name }}
                                        </span>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td class="text-capitalize">{{ $patient->gender }}</td>
                                <td>{{ $patient->phone }}</td>
                                <td>
                                    <span
                                        class="badge {{ $patient->status === 'active' ? 'text-bg-success' : 'text-bg-secondary' }}">
                                        {{ ucfirst($patient->status) }}
                                    </span>
                                </td>
                                <td>{{ $patient->created_at->format('d M Y') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('patients.show', $patient) }}"
                                        class="btn btn-sm btn-outline-secondary" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    @if ($canManage)
                                        <a href="{{ route('patients.edit', $patient) }}"
                                            class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form method="POST" action="{{ route('patients.destroy', $patient) }}"
                                            class="d-inline"
                                            onsubmit="return confirm('Delete patient &quot;{{ $patient->full_name }}&quot;?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No patients found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $patients->links() }}

        </div>
    </div>

@endsection
