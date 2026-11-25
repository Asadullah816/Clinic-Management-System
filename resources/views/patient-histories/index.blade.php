@extends('layouts.app')

@section('title', 'Patient History (Pre-App Records)')

@section('content')

    {{-- Header Banner --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <i class="bi bi-clock-history text-primary me-2"></i>Patient History (Pre-App Records)
            </h4>
            <div class="text-muted small">
                Records of patients who attended the clinic before this software application was launched.
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('payments.outstanding') }}" class="btn btn-outline-danger btn-sm">
                <i class="bi bi-exclamation-circle me-1"></i> Outstanding Dues
            </a>
            <a href="{{ route('patient-histories.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Add Historical Patient Record
            </a>
        </div>
    </div>

    {{-- Financial Metrics Overview Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-body p-3">
                    <div class="text-muted small text-uppercase fw-semibold" style="font-size: 0.72rem;">Total Historical Records</div>
                    <div class="fs-4 fw-bold text-dark mt-1">{{ number_format($totalRecords) }}</div>
                    <div class="small text-muted mt-1">Pre-app visits tracked</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-body p-3">
                    <div class="text-muted small text-uppercase fw-semibold" style="font-size: 0.72rem;">Total Past Value</div>
                    <div class="fs-4 fw-bold text-primary mt-1">PKR {{ number_format($totalCost, 2) }}</div>
                    <div class="small text-muted mt-1">Total previous procedures</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-body p-3">
                    <div class="text-muted small text-uppercase fw-semibold" style="font-size: 0.72rem;">Past Collections</div>
                    <div class="fs-4 fw-bold text-success mt-1">PKR {{ number_format($totalPaid, 2) }}</div>
                    <div class="small text-muted mt-1">Total collected so far</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100 border-start border-danger border-4">
                <div class="card-body p-3">
                    <div class="text-muted small text-uppercase fw-semibold" style="font-size: 0.72rem;">Outstanding Receivables</div>
                    <div class="fs-4 fw-bold text-danger mt-1">PKR {{ number_format($totalDue, 2) }}</div>
                    <div class="small text-danger mt-1 fw-semibold">Pending historical dues</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Records Table Card --}}
    <div class="card shadow-sm border-0">
        <div class="card-body">

            {{-- Filters Bar --}}
            <form method="GET" action="{{ route('patient-histories.index') }}" class="row g-2 mb-3">
                <div class="col-md-4">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm"
                        placeholder="Search patient name, MR#, phone, record #, procedure...">
                </div>
                @if (! auth()->user()->isDoctor() && isset($doctors))
                    <div class="col-md-2">
                        <select name="doctor_id" class="form-select form-select-sm">
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
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Unpaid / Pending</option>
                        <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Partial Due</option>
                        <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Fully Paid</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control form-control-sm"
                        placeholder="From date" title="Visit Date From">
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-search me-1"></i> Filter
                    </button>
                    <a href="{{ route('patient-histories.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset Filters">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Record # &amp; Date</th>
                            <th>Patient</th>
                            <th>Attending Doctor</th>
                            <th>Past Procedures Performed</th>
                            <th>Total (PKR)</th>
                            <th>Paid (PKR)</th>
                            <th>Due (PKR)</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($histories as $history)
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark">{{ $history->record_number }}</div>
                                    <div class="small text-muted"><i class="bi bi-calendar-event me-1"></i>{{ $history->visit_date->format('d M Y') }}</div>
                                </td>
                                <td>
                                    <a href="{{ route('patients.show', $history->patient) }}" class="fw-semibold text-decoration-none text-primary">
                                        {{ $history->patient->full_name }}
                                    </a>
                                    <div class="small text-muted">{{ $history->patient->patient_number }} &bull; {{ $history->patient->phone ?: 'No phone' }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <i class="bi bi-person-badge text-primary me-1"></i>{{ $history->doctor_display_name }}
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark text-truncate" style="max-width: 220px;" title="{{ $history->procedures }}">
                                        {{ $history->procedures }}
                                    </div>
                                    @if ($history->tooth_numbers)
                                        <div class="small text-muted">Teeth: {{ $history->tooth_numbers }}</div>
                                    @endif
                                </td>
                                <td>{{ number_format($history->total_cost, 2) }}</td>
                                <td class="text-success">{{ number_format($history->paid_amount, 2) }}</td>
                                <td class="fw-bold {{ $history->due_amount > 0 ? 'text-danger' : 'text-success' }}">
                                    {{ number_format($history->due_amount, 2) }}
                                </td>
                                <td>
                                    <span class="badge text-bg-{{ $history->statusColor() }}">
                                        {{ $history->statusLabel() }}
                                    </span>
                                </td>
                                <td class="text-end text-nowrap">
                                    @if ($history->due_amount > 0 && $history->whatsappReminderUrl())
                                        <a href="{{ $history->whatsappReminderUrl() }}" target="_blank"
                                            class="btn btn-sm btn-success py-0 px-2 me-1" style="background-color: #25D366; border-color: #25D366;"
                                            title="Send WhatsApp Reminder">
                                            <i class="bi bi-whatsapp"></i>
                                        </a>
                                    @endif
                                    <a href="{{ route('patient-histories.show', $history) }}"
                                        class="btn btn-sm btn-outline-secondary py-0 px-2" title="View details">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('patient-histories.edit', $history) }}"
                                        class="btn btn-sm btn-outline-primary py-0 px-2" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-5">
                                    <i class="bi bi-clock-history fs-2 d-block mb-2 text-muted"></i>
                                    No pre-app historical patient records found.
                                    <div class="mt-2">
                                        <a href="{{ route('patient-histories.create') }}" class="btn btn-sm btn-primary">
                                            <i class="bi bi-plus-lg me-1"></i> Add Historical Patient Record
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($histories->hasPages())
                <div class="mt-3">
                    {{ $histories->links() }}
                </div>
            @endif

        </div>
    </div>

@endsection
