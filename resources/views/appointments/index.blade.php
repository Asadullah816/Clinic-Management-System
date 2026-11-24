@extends('layouts.app')

@section('title', 'Appointments')

@section('content')

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <div>
                <h5 class="mb-0 fw-bold">
                    {{ auth()->user()->isDoctor() ? 'My Appointments Schedule' : 'Appointments Schedule' }}
                </h5>
                @if (auth()->user()->isDoctor())
                    <span class="text-muted small">Showing appointments assigned to you</span>
                @endif
            </div>
            <a href="{{ route('appointments.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Book Appointment
            </a>
        </div>

        <div class="card-body">

            {{-- Quick Filter Pills --}}
            <div class="d-flex flex-wrap gap-2 mb-3 pb-3 border-bottom align-items-center">
                <span class="small fw-semibold text-secondary me-1"><i class="bi bi-funnel me-1"></i> Quick Filter:</span>
                <a href="{{ route('appointments.index') }}"
                    class="btn btn-sm {{ !request('date_filter') && !request('specific_date') ? 'btn-primary' : 'btn-outline-secondary' }} py-0 px-3 rounded-pill">
                    All
                </a>
                <a href="{{ route('appointments.index', ['date_filter' => 'today']) }}"
                    class="btn btn-sm {{ request('date_filter') === 'today' ? 'btn-primary' : 'btn-outline-secondary' }} py-0 px-3 rounded-pill">
                    Today
                </a>
                <a href="{{ route('appointments.index', ['date_filter' => 'tomorrow']) }}"
                    class="btn btn-sm {{ request('date_filter') === 'tomorrow' ? 'btn-primary' : 'btn-outline-secondary' }} py-0 px-3 rounded-pill">
                    Tomorrow
                </a>
                <a href="{{ route('appointments.index', ['date_filter' => 'upcoming']) }}"
                    class="btn btn-sm {{ request('date_filter') === 'upcoming' ? 'btn-primary' : 'btn-outline-secondary' }} py-0 px-3 rounded-pill">
                    Upcoming
                </a>
                <a href="{{ route('appointments.index', ['date_filter' => 'past']) }}"
                    class="btn btn-sm {{ request('date_filter') === 'past' ? 'btn-primary' : 'btn-outline-secondary' }} py-0 px-3 rounded-pill">
                    Past
                </a>
            </div>

            {{-- Filters Form --}}
            <form method="GET" action="{{ route('appointments.index') }}" class="row g-2 mb-3">
                <div class="{{ auth()->user()->isDoctor() ? 'col-md-4' : 'col-md-3' }}">
                    <label class="form-label small fw-semibold text-secondary mb-1">Search Patient</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm"
                        placeholder="Search name, MR #, or phone...">
                </div>
                @if (! auth()->user()->isDoctor())
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold text-secondary mb-1">Doctor</label>
                        <select name="doctor_id" class="form-select form-select-sm">
                            <option value="">All doctors</option>
                            @foreach ($doctors as $doc)
                                <option value="{{ $doc->id }}" {{ (string) request('doctor_id') === (string) $doc->id ? 'selected' : '' }}>
                                    {{ $doc->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-secondary mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All statuses</option>
                        @foreach (\App\Models\Appointment::statuses() as $statusValue => $statusLabel)
                            <option value="{{ $statusValue }}" {{ request('status') === $statusValue ? 'selected' : '' }}>
                                {{ $statusLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="{{ auth()->user()->isDoctor() ? 'col-md-2' : 'col-md-2' }}">
                    <label class="form-label small fw-semibold text-secondary mb-1">Date Period</label>
                    <select name="date_filter" class="form-select form-select-sm">
                        <option value="">All dates</option>
                        <option value="today" {{ request('date_filter') === 'today' ? 'selected' : '' }}>Today</option>
                        <option value="tomorrow" {{ request('date_filter') === 'tomorrow' ? 'selected' : '' }}>Tomorrow</option>
                        <option value="upcoming" {{ request('date_filter') === 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                        <option value="past" {{ request('date_filter') === 'past' ? 'selected' : '' }}>Past</option>
                    </select>
                </div>
                <div class="{{ auth()->user()->isDoctor() ? 'col-md-2' : 'col-md-2' }}">
                    <label class="form-label small fw-semibold text-secondary mb-1">Specific Date</label>
                    <input type="date" name="specific_date" value="{{ request('specific_date') }}" class="form-control form-control-sm"
                        placeholder="Pick exact date">
                </div>
                <div class="{{ auth()->user()->isDoctor() ? 'col-md-2' : 'col-md-1' }} d-flex align-items-end gap-1">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-search me-1"></i> Filter
                    </button>
                    <a href="{{ route('appointments.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset Filters">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Date &amp; Time</th>
                            <th>Patient</th>
                            <th>Doctor</th>
                            <th>Treatment(s)</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($appointments as $appointment)
                            <tr>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $appointment->appointment_date->format('d M Y') }}</div>
                                    <div class="text-muted small"><i class="bi bi-clock me-1"></i>{{ $appointment->formatted_time }}</div>
                                </td>
                                <td>
                                    <a href="{{ route('patients.show', $appointment->patient) }}"
                                        class="fw-semibold text-decoration-none text-primary">
                                        {{ $appointment->patient->full_name }}
                                    </a>
                                    <div class="small text-muted">{{ $appointment->patient->patient_number }} &bull; {{ $appointment->patient->phone ?: 'No phone' }}</div>
                                </td>
                                <td>
                                    @if ($appointment->doctor)
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                            <i class="bi bi-person-badge me-1"></i> {{ $appointment->doctor->name }}
                                        </span>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $appointment->treatment->name ?? '—' }}</div>
                                    @if ($appointment->treatments->count() > 1)
                                        <div class="mt-1 d-flex flex-wrap gap-1">
                                            @foreach ($appointment->treatments->skip(1) as $extraT)
                                                <span class="badge bg-light text-secondary border small">+ {{ $extraT->name }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge text-bg-{{ $appointment->statusColor() }} rounded-pill">
                                        {{ $appointment->statusLabel() }}
                                    </span>
                                </td>
                                <td class="text-end text-nowrap">
                                    @if ($appointment->status === \App\Models\Appointment::STATUS_SCHEDULED)
                                        <a href="{{ route('patient-treatments.create', ['patient_id' => $appointment->patient_id, 'treatment_id' => $appointment->treatment_id, 'appointment_id' => $appointment->id]) }}"
                                            class="btn btn-sm btn-success me-1" title="Record Treatment">
                                            <i class="bi bi-bandaid me-1"></i> Record
                                        </a>
                                    @endif
                                    <a href="{{ route('appointments.show', $appointment) }}"
                                        class="btn btn-sm btn-outline-secondary" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('appointments.edit', $appointment) }}"
                                        class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" action="{{ route('appointments.destroy', $appointment) }}"
                                        class="d-inline" onsubmit="return confirm('Delete this appointment?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">
                                    <i class="bi bi-calendar-x fs-2 d-block text-secondary mb-2"></i>
                                    No appointments found matching the selected filters.
                                    <div class="mt-2">
                                        <a href="{{ route('appointments.create') }}" class="btn btn-sm btn-primary">
                                            <i class="bi bi-plus-lg me-1"></i> Book New Appointment
                                        </a>
                                        <a href="{{ route('appointments.index') }}" class="btn btn-sm btn-outline-secondary ms-1">
                                            Clear Filters
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $appointments->links() }}

        </div>
    </div>

@endsection
