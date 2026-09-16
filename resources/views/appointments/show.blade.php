@extends('layouts.app')

@section('title', 'Appointment Details')

@section('content')

    <div class="card shadow-sm" style="max-width: 760px;">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Appointment Details</h5>
            <span class="badge text-bg-{{ $appointment->statusColor() }}">{{ $appointment->statusLabel() }}</span>
        </div>

        <div class="card-body">
            <table class="table table-sm align-middle">
                <tr>
                    <th class="w-25">Patient</th>
                    <td>
                        <a href="{{ route('patients.show', $appointment->patient) }}" class="text-decoration-none">
                            {{ $appointment->patient->full_name }}
                        </a>
                        <span class="text-muted ms-1">{{ $appointment->patient->patient_number }}</span>
                    </td>
                </tr>
                <tr>
                    <th>Treatment</th>
                    <td>
                        {{ $appointment->treatment->name ?? '—' }}
                        @if ($appointment->treatment?->duration)
                            <span class="text-muted">({{ $appointment->treatment->duration }} min)</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>Date</th>
                    <td>{{ $appointment->appointment_date->format('l, d M Y') }}</td>
                </tr>
                <tr>
                    <th>Time</th>
                    <td>{{ $appointment->appointment_time }}</td>
                </tr>
                <tr>
                    <th>Notes</th>
                    <td>{{ $appointment->notes ?? '—' }}</td>
                </tr>
                <tr>
                    <th>Booked By</th>
                    <td>{{ $appointment->createdBy->name ?? '—' }} on {{ $appointment->created_at->format('d M Y') }}</td>
                </tr>
            </table>

            <div class="d-flex gap-2 mt-3">
                <a href="{{ route('appointments.edit', $appointment) }}" class="btn btn-primary">
                    <i class="bi bi-pencil me-1"></i> Edit
                </a>
                @if ($appointment->status === \App\Models\Appointment::STATUS_COMPLETED)
                    <a href="{{ route('patient-treatments.create', [
                        'patient_id' => $appointment->patient_id,
                        'treatment_id' => $appointment->treatment_id,
                    ]) }}"
                        class="btn btn-success">
                        <i class="bi bi-bandaid me-1"></i> Record as Treatment
                    </a>
                @endif
                <form method="POST" action="{{ route('appointments.destroy', $appointment) }}"
                    onsubmit="return confirm('Delete this appointment?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="bi bi-trash me-1"></i> Delete
                    </button>
                </form>
                <a href="{{ route('appointments.index') }}" class="btn btn-outline-secondary ms-auto">
                    <i class="bi bi-arrow-left me-1"></i> Back to Appointments
                </a>
            </div>
        </div>
    </div>

@endsection
