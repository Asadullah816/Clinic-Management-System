@extends('layouts.app')

@section('title', 'Pre-App Historical Outstandings & Financial Overview')

@section('content')

    {{-- Page Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <i class="bi bi-cash-stack text-danger me-2"></i>Historical Patients Outstandings &amp; Financial Overview
            </h4>
            <div class="text-muted small">
                Track and recover previous balances for patients who attended the clinic before this software launch.
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('payments.outstanding') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-receipt me-1"></i> Current Invoices Outstandings
            </a>
            <a href="{{ route('patient-histories.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-clock-history me-1"></i> All Historical Records
            </a>
            <a href="{{ route('patient-histories.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Add Historical Record
            </a>
        </div>
    </div>

    {{-- Financial Overview Metrics Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-body p-3">
                    <div class="text-muted small text-uppercase fw-semibold" style="font-size: 0.72rem;">Total Past Value</div>
                    <div class="fs-4 fw-bold text-dark mt-1">PKR {{ number_format($totalHistoricalTreatmentValue, 2) }}</div>
                    <div class="small text-muted mt-1">Total previous treatments</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-body p-3">
                    <div class="text-muted small text-uppercase fw-semibold" style="font-size: 0.72rem;">Recovered / Paid</div>
                    <div class="fs-4 fw-bold text-success mt-1">PKR {{ number_format($totalHistoricalPaid, 2) }}</div>
                    <div class="small text-muted mt-1">Collected from previous dues</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100 border-start border-danger border-4">
                <div class="card-body p-3">
                    <div class="text-muted small text-uppercase fw-semibold text-danger" style="font-size: 0.72rem;">Historical Outstandings</div>
                    <div class="fs-3 fw-bold text-danger mt-1">PKR {{ number_format($totalHistoricalOutstanding, 2) }}</div>
                    <div class="small text-danger mt-1 fw-semibold">Receivable balance</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-body p-3">
                    <div class="text-muted small text-uppercase fw-semibold" style="font-size: 0.72rem;">Patients with Pending Dues</div>
                    <div class="fs-4 fw-bold text-warning-emphasis mt-1">{{ number_format($totalPendingCount) }}</div>
                    <div class="small text-muted mt-1">Historical accounts unsettled</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Outstanding Patients Table Card --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark">
                <i class="bi bi-hourglass-split text-danger me-1"></i> Pre-App Outstanding Dues Ledger
            </h5>
            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1 rounded-pill">
                {{ $totalPendingCount }} Pending Balances
            </span>
        </div>

        <div class="card-body">

            {{-- Filter Bar --}}
            <form method="GET" action="{{ route('patient-histories.outstanding') }}" class="row g-2 mb-3">
                <div class="{{ ! auth()->user()->isDoctor() ? 'col-md-5' : 'col-md-8' }}">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm"
                        placeholder="Search patient name, MR#, phone, record #, procedure...">
                </div>
                @if (! auth()->user()->isDoctor() && isset($doctors))
                    <div class="col-md-4">
                        <select name="doctor_id" class="form-select form-select-sm">
                            <option value="">All Attending Doctors</option>
                            @foreach ($doctors as $doc)
                                <option value="{{ $doc->id }}" {{ (string) request('doctor_id') === (string) $doc->id ? 'selected' : '' }}>
                                    {{ $doc->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="{{ ! auth()->user()->isDoctor() ? 'col-md-3' : 'col-md-4' }} d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-search me-1"></i> Filter
                    </button>
                    <a href="{{ route('patient-histories.outstanding') }}" class="btn btn-outline-secondary btn-sm" title="Reset Filters">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Patient</th>
                            <th>Record # &amp; Visit Date</th>
                            <th>Attending Doctor</th>
                            <th>Procedures Performed</th>
                            <th>Total (PKR)</th>
                            <th>Paid (PKR)</th>
                            <th>Due Balance (PKR)</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($outstandings as $record)
                            <tr>
                                <td>
                                    <a href="{{ route('patients.show', $record->patient) }}" class="fw-bold text-decoration-none text-primary">
                                        {{ $record->patient->full_name }}
                                    </a>
                                    <div class="small text-muted">
                                        <span class="badge bg-light text-secondary border me-1">{{ $record->patient->patient_number }}</span>
                                        <i class="bi bi-telephone me-1"></i>{{ $record->patient->phone ?: 'No phone' }}
                                    </div>
                                </td>
                                <td>
                                    <a href="{{ route('patient-histories.show', $record) }}" class="fw-semibold text-dark text-decoration-none">
                                        {{ $record->record_number }}
                                    </a>
                                    <div class="small text-muted"><i class="bi bi-calendar-event me-1"></i>{{ $record->visit_date->format('d M Y') }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <i class="bi bi-person-badge text-primary me-1"></i>{{ $record->doctor_display_name }}
                                    </span>
                                </td>
                                <td>
                                    <div class="small text-dark text-truncate" style="max-width: 200px;" title="{{ $record->procedures }}">
                                        {{ $record->procedures }}
                                    </div>
                                    @if ($record->tooth_numbers)
                                        <div class="small text-muted" style="font-size: 0.75rem;">Teeth: {{ $record->tooth_numbers }}</div>
                                    @endif
                                </td>
                                <td>{{ number_format($record->total_cost, 2) }}</td>
                                <td class="text-success">{{ number_format($record->paid_amount, 2) }}</td>
                                <td class="fw-bold text-danger fs-6">
                                    PKR {{ number_format($record->due_amount, 2) }}
                                </td>
                                <td class="text-end text-nowrap">
                                    {{-- 1-Click WhatsApp Reminder --}}
                                    @if ($record->whatsappReminderUrl())
                                        <a href="{{ $record->whatsappReminderUrl() }}" target="_blank"
                                            class="btn btn-sm btn-success py-1 px-2 me-1" style="background-color: #25D366; border-color: #25D366;"
                                            title="Send WhatsApp Payment Reminder">
                                            <i class="bi bi-whatsapp me-1"></i> Reminder
                                        </a>
                                    @endif

                                    {{-- Receive Payment Modal Button --}}
                                    <button type="button" class="btn btn-sm btn-primary py-1 px-2 me-1"
                                        data-bs-toggle="modal" data-bs-target="#payModal{{ $record->id }}">
                                        <i class="bi bi-cash-coin me-1"></i> Receive
                                    </button>

                                    <a href="{{ route('patient-histories.show', $record) }}" class="btn btn-sm btn-outline-secondary py-1 px-2" title="View details">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>

                            {{-- Payment Modal for this row --}}
                            <div class="modal fade" id="payModal{{ $record->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST" action="{{ route('patient-histories.record-payment', $record) }}">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title fw-bold">
                                                    <i class="bi bi-cash-coin text-success me-2"></i>Receive Historical Payment
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body text-start">
                                                <div class="alert alert-light border small mb-3">
                                                    <div>Patient: <strong>{{ $record->patient->full_name }}</strong> (#{{ $record->patient->patient_number }})</div>
                                                    <div>Historical Record: <strong>{{ $record->record_number }}</strong> (Attended: {{ $record->visit_date->format('d M Y') }})</div>
                                                    <div class="text-danger fw-bold mt-1">Pending Due: PKR {{ number_format($record->due_amount, 2) }}</div>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Payment Amount <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">PKR</span>
                                                        <input type="number" step="0.01" min="1" max="{{ $record->due_amount }}"
                                                            name="amount" value="{{ $record->due_amount }}"
                                                            class="form-control fw-bold" required>
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Payment Date <span class="text-danger">*</span></label>
                                                    <input type="date" name="payment_date" value="{{ today()->toDateString() }}" class="form-control" required>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Payment Method <span class="text-danger">*</span></label>
                                                    <select name="payment_method" class="form-select" required>
                                                        <option value="cash">Cash</option>
                                                        <option value="bank">Bank Transfer</option>
                                                        <option value="card">Credit / Debit Card</option>
                                                        <option value="online">Online / EasyPaisa / JazzCash</option>
                                                        <option value="other">Other</option>
                                                    </select>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Reference / Receipt Number</label>
                                                    <input type="text" name="reference" class="form-control" placeholder="e.g. Receipt #123, Slip ID">
                                                </div>

                                                <div class="mb-0">
                                                    <label class="form-label">Notes</label>
                                                    <textarea name="notes" rows="2" class="form-control" placeholder="Optional notes..."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-success">
                                                    <i class="bi bi-check-lg me-1"></i> Save Payment
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    <i class="bi bi-check-circle-fill text-success fs-2 d-block mb-2"></i>
                                    No pending historical outstandings! All pre-app patient balances are settled.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($outstandings->hasPages())
                <div class="mt-3">
                    {{ $outstandings->links() }}
                </div>
            @endif

        </div>
    </div>

@endsection
