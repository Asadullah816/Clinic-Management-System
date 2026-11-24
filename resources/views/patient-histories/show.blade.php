@extends('layouts.app')

@section('title', 'Historical Record: ' . $history->record_number)

@section('content')

    {{-- Header Card --}}
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary"
                    style="width: 54px; height: 54px;">
                    <i class="bi bi-clock-history fs-3"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <h4 class="mb-0 fw-bold text-dark">{{ $history->record_number }}</h4>
                        <span class="badge text-bg-{{ $history->statusColor() }} px-2 py-1">
                            {{ $history->statusLabel() }}
                        </span>
                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1">
                            <i class="bi bi-shield-check me-1"></i> Pre-App Attendance
                        </span>
                    </div>
                    <div class="text-muted small mt-1">
                        Attended on <strong>{{ $history->visit_date->format('d M Y') }}</strong>
                        &middot; Attending Doctor: <strong class="text-dark">{{ $history->doctor_display_name }}</strong>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                @if ($history->due_amount > 0 && $history->whatsappReminderUrl())
                    <a href="{{ $history->whatsappReminderUrl() }}" target="_blank"
                        class="btn btn-sm btn-success" style="background-color: #25D366; border-color: #25D366;">
                        <i class="bi bi-whatsapp me-1"></i> Send WhatsApp Reminder
                    </a>
                @endif
                <a href="{{ route('patient-histories.edit', $history) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-pencil me-1"></i> Edit
                </a>
                <a href="{{ route('patients.show', $history->patient) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-person me-1"></i> View Full Patient Profile
                </a>
                <a href="{{ route('patient-histories.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- Left Column: Clinical and Treatment Info --}}
        <div class="col-lg-7">
            {{-- Patient Summary Card --}}
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-white py-2 fw-semibold">
                    <i class="bi bi-person-badge text-primary me-1"></i> Patient Information
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fs-5 fw-bold text-dark">{{ $history->patient->full_name }}</span>
                        <span class="badge bg-light text-secondary border">{{ $history->patient->patient_number }}</span>
                    </div>
                    <div class="row g-2 text-muted small">
                        <div class="col-6"><i class="bi bi-telephone me-1"></i> {{ $history->patient->phone ?: 'No phone' }}</div>
                        <div class="col-6"><i class="bi bi-gender-ambiguous me-1"></i> {{ ucfirst($history->patient->gender) }}</div>
                        @if ($history->patient->date_of_birth)
                            <div class="col-6"><i class="bi bi-cake me-1"></i> {{ $history->patient->date_of_birth->format('d M Y') }} ({{ $history->patient->date_of_birth->age }} yrs)</div>
                        @endif
                        @if ($history->patient->address)
                            <div class="col-6"><i class="bi bi-geo-alt me-1"></i> {{ $history->patient->address }}</div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Clinical Procedures & Notes --}}
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-white py-2 fw-semibold">
                    <i class="bi bi-bandaid text-primary me-1"></i> Past Treatment &amp; Clinical Details
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small fw-semibold text-uppercase">Procedures / Treatments Performed</label>
                        <div class="p-3 rounded bg-light border fw-semibold text-dark">
                            {{ $history->procedures }}
                        </div>
                    </div>

                    @if ($history->tooth_numbers)
                        <div class="mb-3">
                            <label class="text-muted small fw-semibold text-uppercase">Tooth Numbers / Region</label>
                            <div><span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 fs-6">{{ $history->tooth_numbers }}</span></div>
                        </div>
                    @endif

                    @if ($history->diagnosis)
                        <div class="mb-3">
                            <label class="text-muted small fw-semibold text-uppercase">Previous Diagnosis / Findings</label>
                            <div class="text-dark">{{ $history->diagnosis }}</div>
                        </div>
                    @endif

                    @if ($history->notes)
                        <div class="mb-0">
                            <label class="text-muted small fw-semibold text-uppercase">Clinical / Historical Notes</label>
                            <div class="p-2 rounded bg-light border text-secondary small">{{ $history->notes }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Right Column: Financial Overview & Payment Collection --}}
        <div class="col-lg-5">
            {{-- Financial Balance Card --}}
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                    <span class="fw-semibold text-dark">
                        <i class="bi bi-cash-stack text-success me-1"></i> Financial Overview
                    </span>
                    <span class="badge bg-light text-secondary border">Previous Dues</span>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted">Total Treatment Cost:</span>
                        <span class="fw-bold fs-5 text-dark">PKR {{ number_format($history->total_cost, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted">Paid So Far:</span>
                        <span class="fw-bold fs-5 text-success">PKR {{ number_format($history->paid_amount, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2 bg-light rounded px-2 mt-2">
                        <span class="fw-bold text-dark">Outstanding Balance:</span>
                        <span class="fw-bold fs-4 {{ $history->due_amount > 0 ? 'text-danger' : 'text-success' }}">
                            PKR {{ number_format($history->due_amount, 2) }}
                        </span>
                    </div>

                    @if ($history->due_amount > 0)
                        <div class="mt-3">
                            <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#receivePaymentModal">
                                <i class="bi bi-cash-coin me-1"></i> Receive Payment Against Balance
                            </button>
                        </div>
                    @else
                        <div class="alert alert-success py-2 px-3 mt-3 mb-0 small text-center">
                            <i class="bi bi-check-circle-fill me-1"></i> Historical balance is fully settled.
                        </div>
                    @endif
                </div>
            </div>

            {{-- Payments History Ledger --}}
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-2 fw-semibold">
                    <i class="bi bi-receipt me-1 text-primary"></i> Payment Records ({{ $history->payments->count() }})
                </div>
                <div class="card-body p-0">
                    @if ($history->payments->isEmpty())
                        <div class="text-center text-muted py-4 small">
                            No payment transactions recorded yet.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Method</th>
                                        <th>Amount (PKR)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($history->payments as $pmt)
                                        <tr>
                                            <td class="small">{{ $pmt->payment_date->format('d M Y') }}</td>
                                            <td>
                                                <span class="badge bg-light text-secondary border text-capitalize small">
                                                    {{ $pmt->payment_method }}
                                                </span>
                                            </td>
                                            <td class="fw-bold text-success small">
                                                PKR {{ number_format($pmt->amount, 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Modal to Receive Payment Against Historical Due --}}
    @if ($history->due_amount > 0)
        <div class="modal fade" id="receivePaymentModal" tabindex="-1" aria-labelledby="receivePaymentModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="{{ route('patient-histories.record-payment', $history) }}">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold" id="receivePaymentModalLabel">
                                <i class="bi bi-cash-coin text-success me-2"></i>Receive Payment
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-light border small mb-3">
                                <div>Patient: <strong>{{ $history->patient->full_name }}</strong> (#{{ $history->patient->patient_number }})</div>
                                <div>Record: <strong>{{ $history->record_number }}</strong></div>
                                <div class="text-danger fw-bold mt-1">Current Outstanding Due: PKR {{ number_format($history->due_amount, 2) }}</div>
                            </div>

                            <div class="mb-3">
                                <label for="amount" class="form-label fw-semibold">Payment Amount <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">PKR</span>
                                    <input type="number" step="0.01" min="1" max="{{ $history->due_amount }}"
                                        id="amount" name="amount" value="{{ old('amount', $history->due_amount) }}"
                                        class="form-control fw-bold" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="payment_date" class="form-label fw-semibold">Payment Date <span class="text-danger">*</span></label>
                                <input type="date" id="payment_date" name="payment_date" value="{{ today()->toDateString() }}"
                                    class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label for="payment_method" class="form-label fw-semibold">Payment Method <span class="text-danger">*</span></label>
                                <select name="payment_method" id="payment_method" class="form-select" required>
                                    <option value="cash">Cash</option>
                                    <option value="bank">Bank Transfer</option>
                                    <option value="card">Credit / Debit Card</option>
                                    <option value="online">Online / EasyPaisa / JazzCash</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="reference" class="form-label">Reference / Receipt Number</label>
                                <input type="text" id="reference" name="reference" class="form-control" placeholder="e.g. Receipt #123, Bank Txn ID">
                            </div>

                            <div class="mb-0">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea id="notes" name="notes" rows="2" class="form-control" placeholder="Optional notes..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-lg me-1"></i> Confirm &amp; Save Payment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

@endsection
