@extends('layouts.app')

@section('title', 'Laser Session ' . $session->invoice_number)

@section('content')
    <div class="row justify-content-center">
        <div class="col-12 col-xl-9">
            <div class="card border shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="mb-0 fw-bold" style="color: #0f172a;">Laser Invoice {{ $session->invoice_number }}</h5>
                            <span class="badge bg-{{ $session->statusColor() }}-subtle text-{{ $session->statusColor() }} border border-{{ $session->statusColor() }}-subtle">
                                {{ $session->statusLabel() }}
                            </span>
                        </div>
                        <p class="text-muted small mb-0 mt-1">Date: {{ $session->session_date->format('d M Y') }} &bull; Created by {{ $session->createdBy->name ?? 'Staff' }}</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('laser.sessions.print', $session) }}" class="btn btn-dark btn-sm d-inline-flex align-items-center gap-1">
                            <i class="bi bi-printer"></i> Print Receipt
                        </a>
                        <a href="{{ route('laser.sessions.edit', $session) }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                    </div>
                </div>

                <div class="card-body p-4">
                    {{-- Patient & Procedure Summary --}}
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 border h-100">
                                <span class="text-muted small text-uppercase fw-semibold d-block mb-1">Laser Client:</span>
                                <h5 class="fw-bold text-dark mb-1">
                                    <a href="{{ route('laser.patients.show', $session->patient) }}" class="text-decoration-none text-dark">
                                        {{ $session->patient->full_name }}
                                    </a>
                                </h5>
                                <div class="small text-muted mb-2">
                                    Patient ID: <strong>{{ $session->patient->patient_number }}</strong> &bull; {{ $session->patient->phone }}
                                </div>
                                @if ($session->patient->skin_type)
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle small">
                                        <i class="bi bi-sun me-1"></i> {{ $session->patient->skin_type }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 border h-100">
                                <span class="text-muted small text-uppercase fw-semibold d-block mb-1">Procedure Details:</span>
                                <h5 class="fw-bold text-primary mb-1">{{ $session->treatment->name ?? 'Custom Procedure' }}</h5>
                                <div class="small text-muted mb-1">
                                    Progress: <strong>Session {{ $session->session_number }}</strong> of {{ $session->total_sessions ?? 'Ongoing' }}
                                </div>
                                <div class="small text-muted">
                                    Operator: <strong>{{ $session->performedBy->name ?? 'Laser Specialist' }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Technical Parameters Grid --}}
                    <h6 class="fw-bold text-secondary text-uppercase small mb-3">
                        <i class="bi bi-sliders me-1"></i> Technical &amp; Machine Parameters
                    </h6>
                    <div class="row g-2 mb-4 text-center">
                        <div class="col-6 col-sm-4 col-md-2">
                            <div class="p-2 border rounded bg-white">
                                <span class="text-muted d-block" style="font-size: 11px;">Machine</span>
                                <span class="fw-bold small text-truncate d-block" title="{{ $session->laser_machine ?? 'Standard' }}">
                                    {{ $session->laser_machine ? strtok($session->laser_machine, ' ') : 'Diode' }}
                                </span>
                            </div>
                        </div>
                        <div class="col-6 col-sm-4 col-md-2">
                            <div class="p-2 border rounded bg-white">
                                <span class="text-muted d-block" style="font-size: 11px;">Fluence</span>
                                <span class="fw-bold small">{{ $session->fluence ?: '—' }}</span>
                            </div>
                        </div>
                        <div class="col-6 col-sm-4 col-md-2">
                            <div class="p-2 border rounded bg-white">
                                <span class="text-muted d-block" style="font-size: 11px;">Pulse Width</span>
                                <span class="fw-bold small">{{ $session->pulse_width ?: '—' }}</span>
                            </div>
                        </div>
                        <div class="col-6 col-sm-4 col-md-2">
                            <div class="p-2 border rounded bg-white">
                                <span class="text-muted d-block" style="font-size: 11px;">Spot Size</span>
                                <span class="fw-bold small">{{ $session->spot_size ?: '—' }}</span>
                            </div>
                        </div>
                        <div class="col-6 col-sm-4 col-md-2">
                            <div class="p-2 border rounded bg-white">
                                <span class="text-muted d-block" style="font-size: 11px;">Shots Fired</span>
                                <span class="fw-bold small">{{ $session->pulses_count ? number_format($session->pulses_count) : '—' }}</span>
                            </div>
                        </div>
                        <div class="col-6 col-sm-4 col-md-2">
                            <div class="p-2 border rounded bg-white">
                                <span class="text-muted d-block" style="font-size: 11px;">Payment Method</span>
                                <span class="fw-bold small text-capitalize">{{ $session->methodLabel() }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Billing Table --}}
                    <h6 class="fw-bold text-secondary text-uppercase small mb-3">
                        <i class="bi bi-receipt me-1"></i> Financial Breakdown &amp; Separate Discount
                    </h6>
                    <table class="table table-bordered mb-4">
                        <tr>
                            <th class="w-50 bg-light">Standard Procedure Fee</th>
                            <td class="text-end">PKR {{ number_format($session->price, 2) }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">
                                Laser Discount
                                @if ($session->discount_type === 'percentage' && $session->discount_percentage)
                                    <span class="badge bg-secondary-subtle text-secondary ms-1">{{ $session->discount_percentage }}%</span>
                                @endif
                            </th>
                            <td class="text-end text-danger">&minus; PKR {{ number_format($session->discount, 2) }}</td>
                        </tr>
                        <tr class="table-light">
                            <th class="fs-6">Net Procedure Total</th>
                            <td class="text-end fw-bold fs-6">PKR {{ number_format($session->total_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">Amount Received</th>
                            <td class="text-end text-success fw-bold">PKR {{ number_format($session->paid_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">Outstanding Balance</th>
                            <td class="text-end fw-bold {{ $session->due_amount > 0 ? 'text-danger' : 'text-dark' }}">
                                PKR {{ number_format($session->due_amount, 2) }}
                            </td>
                        </tr>
                    </table>

                    @if ($session->notes)
                        <div class="p-3 bg-light rounded border mb-4">
                            <span class="text-muted text-uppercase fw-semibold small d-block mb-1">Clinical Observations:</span>
                            <div class="small">{{ $session->notes }}</div>
                        </div>
                    @endif

                    {{-- Bottom Action Buttons --}}
                    <div class="d-flex flex-wrap justify-content-between align-items-center pt-3 border-top gap-2">
                        <div class="d-flex gap-2">
                            <a href="{{ route('laser.sessions.print', $session) }}" class="btn btn-dark">
                                <i class="bi bi-printer me-1"></i> Print Receipt (80mm / A4)
                            </a>
                            <a href="{{ route('laser.sessions.edit', $session) }}" class="btn btn-outline-primary">
                                <i class="bi bi-pencil me-1"></i> Edit Session
                            </a>
                            <form method="POST" action="{{ route('laser.sessions.destroy', $session) }}"
                                  onsubmit="return confirm('Delete laser session {{ $session->invoice_number }}?');" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger">
                                    <i class="bi bi-trash me-1"></i> Delete
                                </button>
                            </form>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('laser.patients.show', $session->patient) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-person me-1"></i> View Patient Profile
                            </a>
                            <a href="{{ route('laser.sessions.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i> All Sessions
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
