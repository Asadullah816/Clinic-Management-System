@extends('layouts.app')

@section('title', 'Appointment Details')

@section('content')

    <div class="card shadow-sm">
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
                    <th>Treatment(s)</th>
                    <td>
                        @if ($appointment->treatments->isNotEmpty())
                            <div class="d-flex flex-column gap-1">
                                @foreach ($appointment->treatments as $idx => $t)
                                    <div>
                                        <span class="badge {{ $idx === 0 ? 'bg-primary' : 'bg-secondary' }} me-1">
                                            {{ $idx === 0 ? 'Primary' : 'Additional' }}
                                        </span>
                                        <span class="fw-semibold text-dark">{{ $t->name }}</span>
                                        <span class="text-muted small">&bull; PKR {{ number_format($t->price, 2) }}</span>
                                        @if ($t->duration)
                                            <span class="text-muted small">({{ $t->duration }} min)</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            {{ $appointment->treatment->name ?? '—' }}
                            @if ($appointment->treatment?->duration)
                                <span class="text-muted">({{ $appointment->treatment->duration }} min)</span>
                            @endif
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>Date</th>
                    <td>{{ $appointment->appointment_date->format('l, d M Y') }}</td>
                </tr>
                <tr>
                    <th>Time</th>
                    <td><i class="bi bi-clock me-1 text-muted"></i>{{ $appointment->formatted_time }}</td>
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
                @if (in_array($appointment->status, [\App\Models\Appointment::STATUS_SCHEDULED, \App\Models\Appointment::STATUS_COMPLETED]))
                    <a href="{{ route('patient-treatments.create', [
                        'patient_id' => $appointment->patient_id,
                        'treatment_id' => $appointment->treatment_id,
                        'appointment_id' => $appointment->id,
                    ]) }}"
                        class="btn btn-success">
                        <i class="bi bi-bandaid me-1"></i> Record Treatment
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
