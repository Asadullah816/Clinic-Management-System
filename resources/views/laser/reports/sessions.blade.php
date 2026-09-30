@extends('layouts.app')

@section('title', 'Laser Sessions Report')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3 no-print">
        <div>
            <h4 class="mb-0 fw-bold" style="color: #0f172a;">Laser Treatment Sessions Report</h4>
            <p class="text-muted small mb-0">Detailed activity, patient visits, and revenue collected by procedure.</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-dark d-inline-flex align-items-center gap-1 shadow-sm" onclick="window.print()">
                <i class="bi bi-printer"></i> Print Report
            </button>
            <a href="{{ route('laser.dashboard') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Dashboard
            </a>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4 no-print">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('laser.reports.sessions') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-4">
                    <label class="form-label small text-muted mb-0">Procedure</label>
                    <select name="treatment_id" class="form-select form-select-sm">
                        <option value="">All Procedures</option>
                        @foreach ($treatments as $treatment)
                            <option value="{{ $treatment->id }}" {{ request('treatment_id') == $treatment->id ? 'selected' : '' }}>
                                {{ $treatment->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small text-muted mb-0">From</label>
                    <input type="date" name="from" class="form-control form-control-sm" value="{{ $from }}">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small text-muted mb-0">To</label>
                    <input type="date" name="to" class="form-control form-control-sm" value="{{ $to }}">
                </div>
                <div class="col-12 col-md-2 d-flex align-items-end gap-1">
                    <button type="submit" class="btn btn-sm btn-dark w-100">Filter</button>
                    @if (request()->hasAny(['treatment_id', 'from', 'to']))
                        <a href="{{ route('laser.reports.sessions') }}" class="btn btn-sm btn-outline-secondary" title="Reset">
                            <i class="bi bi-x"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="p-3 border rounded-3 bg-white shadow-sm">
                <span class="text-muted small text-uppercase fw-semibold d-block">Total Sessions Performed</span>
                <h3 class="fw-bold text-primary mb-0">{{ number_format($totalSessions) }}</h3>
            </div>
        </div>
        <div class="col-md-6">
            <div class="p-3 border rounded-3 bg-white shadow-sm">
                <span class="text-muted small text-uppercase fw-semibold d-block">Revenue Collected</span>
                <h3 class="fw-bold text-success mb-0">PKR {{ number_format($totalPaid, 2) }}</h3>
            </div>
        </div>
    </div>

    {{-- Sessions Table --}}
    <div class="card border shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-uppercase text-secondary">
                        <tr>
                            <th class="ps-3">Date / Invoice</th>
                            <th>Patient</th>
                            <th>Procedure</th>
                            <th>Session #</th>
                            <th>Price</th>
                            <th>Discount</th>
                            <th>Paid (PKR)</th>
                            <th>Operator</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sessions as $session)
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-bold">{{ $session->session_date->format('d M Y') }}</div>
                                    <code>{{ $session->invoice_number }}</code>
                                </td>
                                <td>
                                    <div class="fw-medium">{{ $session->patient->full_name }}</div>
                                    <div class="text-muted small">{{ $session->patient->patient_number }}</div>
                                </td>
                                <td>{{ $session->treatment->name ?? 'Custom' }}</td>
                                <td>#{{ $session->session_number }}</td>
                                <td>PKR {{ number_format($session->price, 0) }}</td>
                                <td class="text-danger">&minus;{{ number_format($session->discount, 0) }}</td>
                                <td class="fw-bold text-success">PKR {{ number_format($session->paid_amount, 2) }}</td>
                                <td class="small text-muted">{{ $session->performedBy->name ?? 'Staff' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">No sessions found for this period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($sessions->hasPages())
            <div class="card-footer bg-white py-3 no-print">
                {{ $sessions->links() }}
            </div>
        @endif
    </div>

    <style>
        @media print {
            .no-print, .sidebar, .app-navbar, .offcanvas {
                display: none !important;
            }
        }
    </style>
@endsection
