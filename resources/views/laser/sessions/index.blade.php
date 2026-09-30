@extends('layouts.app')

@section('title', 'Laser Sessions')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="mb-0 fw-bold" style="color: #0f172a;">Laser Treatment Sessions</h4>
            <p class="text-muted small mb-0">Record of laser sessions, machine parameters, billing (LINV), and separate discounts.</p>
        </div>
        <a href="{{ route('laser.sessions.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-plus-circle"></i> Record Laser Session
        </a>
    </div>

    {{-- Filters Card --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('laser.sessions.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0"
                               placeholder="Search invoice #, patient name, ID..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <select name="treatment_id" class="form-select form-select-sm">
                        <option value="">All Procedures</option>
                        @foreach ($treatments as $treatment)
                            <option value="{{ $treatment->id }}" {{ request('treatment_id') == $treatment->id ? 'selected' : '' }}>
                                {{ $treatment->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Partial</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <input type="date" name="date" class="form-control form-control-sm" value="{{ request('date') }}">
                </div>
                <div class="col-6 col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-dark w-100">Filter</button>
                    @if (request()->hasAny(['search', 'treatment_id', 'status', 'date']))
                        <a href="{{ route('laser.sessions.index') }}" class="btn btn-sm btn-outline-secondary" title="Reset">
                            <i class="bi bi-x"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Sessions Table --}}
    <div class="card border shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-uppercase text-secondary">
                        <tr>
                            <th class="ps-3">Invoice / Date</th>
                            <th>Laser Patient</th>
                            <th>Procedure</th>
                            <th>Session #</th>
                            <th>Total (PKR)</th>
                            <th>Paid / Due</th>
                            <th>Status</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sessions as $session)
                            <tr>
                                <td class="ps-3">
                                    <a href="{{ route('laser.sessions.show', $session) }}" class="fw-bold text-decoration-none">
                                        {{ $session->invoice_number }}
                                    </a>
                                    <div class="text-muted small">{{ $session->session_date->format('d M Y') }}</div>
                                </td>
                                <td>
                                    <a href="{{ route('laser.patients.show', $session->patient) }}" class="text-dark text-decoration-none fw-semibold">
                                        {{ $session->patient->full_name }}
                                    </a>
                                    <div class="text-muted small">
                                        {{ $session->patient->patient_number }}
                                        @if ($session->patient->skin_type)
                                            &bull; <span class="badge bg-light text-dark border">{{ $session->patient->skin_type }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-medium">{{ $session->treatment->name ?? 'Custom Laser' }}</div>
                                    @if ($session->fluence)
                                        <div class="text-muted small">{{ $session->fluence }} @if($session->pulses_count) &bull; {{ $session->pulses_count }} shots @endif</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        #{{ $session->session_number }}{{ $session->total_sessions ? ' / ' . $session->total_sessions : '' }}
                                    </span>
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
                                        <a href="{{ route('laser.sessions.edit', $session) }}" class="btn btn-outline-secondary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-activity fs-1 d-block mb-2 text-secondary"></i>
                                    No laser sessions found.
                                    <div class="mt-2">
                                        <a href="{{ route('laser.sessions.create') }}" class="btn btn-sm btn-primary">
                                            <i class="bi bi-plus-circle me-1"></i> Record New Laser Session
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($sessions->hasPages())
            <div class="card-footer bg-white py-3">
                {{ $sessions->links() }}
            </div>
        @endif
    </div>
@endsection
