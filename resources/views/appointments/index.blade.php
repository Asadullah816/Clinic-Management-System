@extends('layouts.app')

@section('title', 'Appointments')

@section('content')

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0">Appointments</h5>
            <a href="{{ route('appointments.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Add Appointment
            </a>
        </div>

        <div class="card-body">

            {{-- Filters --}}
            <form method="GET" action="{{ route('appointments.index') }}" class="row g-2 mb-3">
                <div class="col-md-4">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                        placeholder="Search patient name or #...">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All statuses</option>
                        @foreach (\App\Models\Appointment::statuses() as $statusValue => $statusLabel)
                            <option value="{{ $statusValue }}" {{ request('status') === $statusValue ? 'selected' : '' }}>
                                {{ $statusLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="date_filter" class="form-select">
                        <option value="">All dates</option>
                        <option value="today" {{ request('date_filter') === 'today' ? 'selected' : '' }}>Today</option>
                        <option value="upcoming" {{ request('date_filter') === 'upcoming' ? 'selected' : '' }}>Upcoming
                        </option>
                        <option value="past" {{ request('date_filter') === 'past' ? 'selected' : '' }}>Past</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-search me-1"></i> Filter
                    </button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Date &amp; Time</th>
                            <th>Patient</th>
                            <th>Treatment</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($appointments as $appointment)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $appointment->appointment_date->format('d M Y') }}</div>
                                    <div class="text-muted small">{{ $appointment->appointment_time }}</div>
                                </td>
                                <td>
                                    <a href="{{ route('patients.show', $appointment->patient) }}"
                                        class="text-decoration-none">
                                        {{ $appointment->patient->full_name }}
                                    </a>
                                </td>
                                <td>{{ $appointment->treatment->name ?? '—' }}</td>
                                <td>
                                    <span class="badge text-bg-{{ $appointment->statusColor() }}">
                                        {{ $appointment->statusLabel() }}
                                    </span>
                                </td>
                                <td class="text-end">
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
                                <td colspan="5" class="text-center text-muted py-4">No appointments found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $appointments->links() }}

        </div>
    </div>

@endsection
