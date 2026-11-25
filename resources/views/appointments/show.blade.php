@extends('layouts.app')

@section('title', 'Appointment Details')

@section('content')

    <div class="card shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">Appointment Details</h5>
            <div class="d-flex align-items-center gap-2">
                <span class="badge text-bg-{{ $appointment->statusColor() }} px-3 py-2 fs-6">{{ $appointment->statusLabel() }}</span>
                @if ($appointment->status === \App\Models\Appointment::STATUS_SCHEDULED && $appointment->whatsappReminderUrl())
                    <a href="{{ $appointment->whatsappReminderUrl() }}" target="_blank"
                        class="btn btn-sm text-white shadow-sm d-inline-flex align-items-center px-3 py-1"
                        style="background-color: #25D366; border: 1px solid #20ba5a; color: #fff !important; font-weight: 600;"
                        title="Send WhatsApp Appointment Reminder">
                        <i class="bi bi-whatsapp me-1 fs-6"></i> WhatsApp Reminder
                    </a>
                @endif
            </div>
        </div>

        <div class="card-body">
            <table class="table table-sm align-middle">
                <tr>
                    <th class="w-25">Patient</th>
                    <td>
                        <a href="{{ route('patients.show', $appointment->patient) }}" class="text-decoration-none fw-semibold">
                            {{ $appointment->patient->full_name }}
                        </a>
                        <span class="text-muted ms-1">({{ $appointment->patient->patient_number }})</span>
                        @if ($appointment->patient->phone)
                            <span class="ms-3 text-secondary small"><i class="bi bi-telephone me-1"></i>{{ $appointment->patient->phone }}</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>Attending Doctor</th>
                    <td>
                        @if ($appointment->doctor)
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                <i class="bi bi-person-badge me-1"></i> {{ $appointment->doctor->name }}
                            </span>
                        @else
                            <span class="text-muted">— (General)</span>
                        @endif
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

            <div class="d-flex flex-wrap gap-2 mt-3 align-items-center">
                @if ($appointment->status === \App\Models\Appointment::STATUS_SCHEDULED && $appointment->whatsappReminderUrl())
                    <a href="{{ $appointment->whatsappReminderUrl() }}" target="_blank"
                        class="btn btn-success shadow-sm d-inline-flex align-items-center px-3"
                        style="background-color: #25D366; border-color: #20ba5a; color: #fff !important; font-weight: 600;"
                        title="Send WhatsApp Appointment Reminder">
                        <i class="bi bi-whatsapp me-2 fs-5"></i> Send WhatsApp Reminder
                    </a>
                @endif
                <a href="{{ route('appointments.edit', $appointment) }}" class="btn btn-primary">
                    <i class="bi bi-pencil me-1"></i> Edit
                </a>
                @if ($appointment->status === \App\Models\Appointment::STATUS_SCHEDULED)
                    <a href="{{ route('patient-treatments.create', [
                        'patient_id' => $appointment->patient_id,
                        'treatment_id' => $appointment->treatment_id,
                        'appointment_id' => $appointment->id,
                    ]) }}"
                        class="btn btn-outline-success">
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
