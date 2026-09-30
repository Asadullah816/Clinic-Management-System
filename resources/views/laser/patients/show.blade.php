@extends('layouts.app')

@section('title', $patient->full_name . ' (' . $patient->patient_number . ')')

@section('content')
    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h4 class="mb-0 fw-bold" style="color: #0f172a;">{{ $patient->full_name }}</h4>
                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle fs-6">
                    {{ $patient->patient_number }}
                </span>
                <span class="badge bg-{{ $patient->statusColor() }}-subtle text-{{ $patient->statusColor() }} border border-{{ $patient->statusColor() }}-subtle">
                    {{ ucfirst($patient->status) }}
                </span>
            </div>
            <p class="text-muted small mb-0 mt-1">Laser Unit Patient Profile &bull; Registered {{ $patient->created_at->format('d M Y') }}</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('laser.sessions.create', ['patient_id' => $patient->id]) }}" class="btn btn-success d-inline-flex align-items-center gap-1 shadow-sm">
                <i class="bi bi-plus-circle"></i> Record Laser Session
            </a>
            <a href="{{ route('laser.patients.edit', $patient) }}" class="btn btn-outline-primary d-inline-flex align-items-center gap-1 shadow-sm">
                <i class="bi bi-pencil"></i> Edit Profile
            </a>
            <a href="{{ route('laser.patients.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> All Patients
            </a>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-4">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
                <span class="text-muted small text-uppercase fw-semibold">Laser Sessions</span>
                <h3 class="fw-bold mb-0 text-primary">{{ $patient->sessions->count() }}</h3>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
                <span class="text-muted small text-uppercase fw-semibold">Total Paid</span>
                <h3 class="fw-bold mb-0 text-success">PKR {{ number_format($patient->totalSpent(), 2) }}</h3>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
                <span class="text-muted small text-uppercase fw-semibold">Outstanding Due</span>
                <h3 class="fw-bold mb-0 {{ $patient->totalDue() > 0 ? 'text-danger' : 'text-dark' }}">
                    PKR {{ number_format($patient->totalDue(), 2) }}
                </h3>
            </div>
        </div>
    </div>

    {{-- Patient Information & Notes --}}
    <div class="row g-4 mb-4">
        <div class="col-12 col-lg-5">
            <div class="card border shadow-sm rounded-3 h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-person-lines-fill me-1 text-primary"></i> Patient Details</h6>
                </div>
                <div class="card-body p-3">
                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Gender / Age</span>
                            <span class="fw-semibold">{{ ucfirst($patient->gender) }} {{ $patient->age ? '• ' . $patient->age . ' yrs' : '' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Date of Birth</span>
                            <span>{{ $patient->date_of_birth ? $patient->date_of_birth->format('d M Y') : '—' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Phone</span>
                            <span class="fw-semibold">{{ $patient->phone }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Email</span>
                            <span>{{ $patient->email ?? '—' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Emergency Contact</span>
                            <span>{{ $patient->emergency_contact ?? '—' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Address</span>
                            <span class="text-end" style="max-width: 60%;">{{ $patient->address ?? '—' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Referred By</span>
                            <span>{{ $patient->referred_by ?? '—' }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-7">
            <div class="card border shadow-sm rounded-3 h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-shield-exclamation me-1 text-danger"></i> Skin Phototype &amp; Clinical Notes</h6>
                </div>
                <div class="card-body p-3">
                    <div class="mb-3">
                        <span class="text-muted small text-uppercase fw-semibold d-block mb-1">Fitzpatrick Scale:</span>
                        @if ($patient->skin_type)
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle fs-6 py-2 px-3">
                                <i class="bi bi-sun me-1"></i> {{ $patient->skin_type }}
                            </span>
                        @else
                            <span class="text-muted small">Not specified</span>
                        @endif
                    </div>

                    <div class="p-3 bg-light rounded border">
                        <span class="text-muted small text-uppercase fw-semibold d-block mb-1">Contraindications &amp; Laser History:</span>
                        <p class="mb-0 small text-dark" style="white-space: pre-line;">{{ $patient->medical_notes ?: 'No contraindications or special clinical notes recorded.' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Sessions History Table --}}
    <div class="card border shadow-sm rounded-3">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
            <h5 class="mb-0 fw-bold"><i class="bi bi-clock-history me-1 text-primary"></i> Laser Treatment &amp; Session History</h5>
            <a href="{{ route('laser.sessions.create', ['patient_id' => $patient->id]) }}" class="btn btn-sm btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Add Session
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-uppercase text-secondary">
                        <tr>
                            <th class="ps-3">Invoice / Date</th>
                            <th>Procedure</th>
                            <th>Session #</th>
                            <th>Laser Settings</th>
                            <th>Total (PKR)</th>
                            <th>Paid / Due</th>
                            <th>Status</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($patient->sessions as $session)
                            <tr>
                                <td class="ps-3">
                                    <a href="{{ route('laser.sessions.show', $session) }}" class="fw-bold text-decoration-none">
                                        {{ $session->invoice_number }}
                                    </a>
                                    <div class="text-muted small">{{ $session->session_date->format('d M Y') }}</div>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $session->treatment->name ?? 'Custom Procedure' }}</div>
                                    <div class="text-muted small">{{ $session->laser_machine ?? 'Laser Machine' }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        Session {{ $session->session_number }}{{ $session->total_sessions ? ' / ' . $session->total_sessions : '' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="small">
                                        @if ($session->fluence) <span>Fluence: <strong>{{ $session->fluence }}</strong></span><br> @endif
                                        @if ($session->pulses_count) <span class="text-muted">Shots: {{ number_format($session->pulses_count) }}</span> @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold">PKR {{ number_format($session->total_amount, 2) }}</div>
                                    @if ($session->discount > 0)
                                        <div class="text-danger small" style="font-size: 11px;">Disc: &minus;{{ number_format($session->discount, 0) }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-success small fw-semibold">Paid: PKR {{ number_format($session->paid_amount, 2) }}</div>
                                    @if ($session->due_amount > 0)
                                        <div class="text-danger small fw-bold">Due: PKR {{ number_format($session->due_amount, 2) }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $session->statusColor() }}-subtle text-{{ $session->statusColor() }} border border-{{ $session->statusColor() }}-subtle">
                                        {{ $session->statusLabel() }}
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('laser.sessions.print', $session) }}" class="btn btn-outline-dark" title="Print 80mm Receipt">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                        <a href="{{ route('laser.sessions.show', $session) }}" class="btn btn-outline-primary" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    No laser sessions recorded yet for this patient.
                                    <div class="mt-2">
                                        <a href="{{ route('laser.sessions.create', ['patient_id' => $patient->id]) }}" class="btn btn-sm btn-primary">
                                            <i class="bi bi-plus-circle me-1"></i> Record First Session
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
