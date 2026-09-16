@extends('layouts.app')

@section('title', 'Patient Profile')

@section('content')

    @php
        $user = auth()->user();

        // Tab visibility follows the Roles & Access matrix
        $showMedicalHistory = $user->hasRole('admin', 'staff');
        $showAppointments = $user->hasRole('admin', 'receptionist', 'staff');
        $showTreatments = $user->hasRole('admin', 'receptionist', 'staff');
        $showBilling = $user->hasRole('admin', 'accountant', 'receptionist');
        $showUsage = $user->hasRole('admin', 'staff');
    @endphp

    {{-- Patient header card --}}
    <div class="card shadow-sm mb-3">
        <div class="card-body d-flex flex-wrap align-items-center gap-3">
            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center"
                style="width: 64px; height: 64px;">
                <i class="bi bi-person fs-2"></i>
            </div>

            <div class="me-auto">
                <h4 class="mb-0">
                    {{ $patient->full_name }}
                    <span class="badge {{ $patient->status === 'active' ? 'text-bg-success' : 'text-bg-secondary' }} ms-1">
                        {{ ucfirst($patient->status) }}
                    </span>
                </h4>
                <div class="text-muted">
                    <span class="me-3"><i class="bi bi-hash me-1"></i>{{ $patient->patient_number }}</span>
                    <span class="me-3 text-capitalize"><i
                            class="bi bi-gender-ambiguous me-1"></i>{{ $patient->gender }}</span>
                    @if ($patient->date_of_birth)
                        <span class="me-3"><i class="bi bi-cake me-1"></i>{{ $patient->date_of_birth->format('d M Y') }}
                            ({{ $patient->date_of_birth->age }} yrs)</span>
                    @endif
                    <span class="me-3"><i class="bi bi-telephone me-1"></i>{{ $patient->phone }}</span>
                    @if ($patient->email)
                        <span><i class="bi bi-envelope me-1"></i>{{ $patient->email }}</span>
                    @endif
                </div>
            </div>

            <div class="d-flex gap-2">
                @if ($user->hasRole('admin', 'receptionist', 'staff'))
                    <a href="{{ route('patients.edit', $patient) }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-pencil me-1"></i> Edit
                    </a>
                @endif
                <a href="{{ route('patients.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>
    </div>

    {{-- Profile tabs --}}
    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab">
                <i class="bi bi-info-circle me-1"></i> Overview
            </button>
        </li>

        @if ($showMedicalHistory)
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#medical-history" type="button"
                    role="tab">
                    <i class="bi bi-clipboard2-pulse me-1"></i> Medical History
                    <span class="badge text-bg-secondary ms-1">{{ $patient->medicalHistories->count() }}</span>
                </button>
            </li>
        @endif

        @if ($showAppointments)
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#appointments" type="button" role="tab">
                    <i class="bi bi-calendar-check me-1"></i> Appointments
                    <span class="badge text-bg-secondary ms-1">{{ $patient->appointments->count() }}</span>
                </button>
            </li>
        @endif

        @if ($showTreatments)
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#treatments" type="button" role="tab">
                    <i class="bi bi-bandaid me-1"></i> Treatments
                    <span class="badge text-bg-secondary ms-1">{{ $patient->patientTreatments->count() }}</span>
                </button>
            </li>
        @endif

        @if ($showBilling)
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#billing" type="button" role="tab">
                    <i class="bi bi-receipt me-1"></i> Billing
                    <span class="badge text-bg-secondary ms-1">{{ $patient->invoices->count() }}</span>
                </button>
            </li>
        @endif

        @if ($showUsage)
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#usage" type="button" role="tab">
                    <i class="bi bi-capsule me-1"></i> Product Usage
                    <span class="badge text-bg-secondary ms-1">{{ $patient->medicineUsages->count() }}</span>
                </button>
            </li>
        @endif
    </ul>

    <div class="tab-content bg-white border border-top-0 rounded-bottom shadow-sm p-4">

        {{-- ================= OVERVIEW ================= --}}
        <div class="tab-pane fade show active" id="overview" role="tabpanel">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-muted text-uppercase small">Contact</h6>
                    <table class="table table-sm">
                        <tr>
                            <th class="w-25">Address</th>
                            <td>{{ $patient->address ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>Emergency</th>
                            <td>{{ $patient->emergency_contact ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>Occupation</th>
                            <td>{{ $patient->occupation ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>Referred By</th>
                            <td>{{ $patient->referred_by ?? '—' }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6 class="text-muted text-uppercase small">Medical</h6>
                    <table class="table table-sm">
                        <tr>
                            <th class="w-25">Skin Type</th>
                            <td>{{ $patient->skin_type ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>Allergies</th>
                            <td>{{ $patient->allergies ?? 'None recorded' }}</td>
                        </tr>
                        <tr>
                            <th>Notes</th>
                            <td>{{ $patient->medical_notes ?? '—' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- Financial summary — real numbers arrive in Phase 8 (Payments) --}}
            <h6 class="text-muted text-uppercase small mt-3">Financial Summary</h6>
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="card bg-light border-0">
                        <div class="card-body py-3">
                            <div class="text-muted small">Total Treatment Cost</div>
                            <div class="fs-5 fw-semibold text-muted">Coming in Phase 6</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light border-0">
                        <div class="card-body py-3">
                            <div class="text-muted small">Total Invoiced</div>
                            <div class="fs-5 fw-semibold text-muted">Coming in Phase 7</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light border-0">
                        <div class="card-body py-3">
                            <div class="text-muted small">Total Paid</div>
                            <div class="fs-5 fw-semibold text-muted">Coming in Phase 8</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light border-0">
                        <div class="card-body py-3">
                            <div class="text-muted small">Outstanding</div>
                            <div class="fs-5 fw-semibold text-muted">Coming in Phase 8</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= MEDICAL HISTORY ================= --}}
        @if ($showMedicalHistory)
            <div class="tab-pane fade" id="medical-history" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">Medical History Records</h6>
                    <a href="{{ route('medical-histories.create', $patient) }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-lg me-1"></i> Add Record
                    </a>
                </div>

                @if ($patient->medicalHistories->isEmpty())
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-clipboard2-pulse fs-1 d-block mb-2"></i>
                        No medical history records yet.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Visit Date</th>
                                    <th>Chief Complaint</th>
                                    <th>Diagnosis</th>
                                    <th>Current Medications</th>
                                    <th>Recorded By</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($patient->medicalHistories as $medicalHistory)
                                    <tr>
                                        <td>{{ $medicalHistory->visit_date->format('d M Y') }}</td>
                                        <td>{{ $medicalHistory->chief_complaint }}</td>
                                        <td>{{ \Illuminate\Support\Str::limit($medicalHistory->diagnosis ?? '—', 40) }}
                                        </td>
                                        <td>{{ \Illuminate\Support\Str::limit($medicalHistory->current_medications ?? '—', 25) }}
                                        </td>
                                        <td>{{ $medicalHistory->createdBy->name ?? '—' }}</td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                                title="View details" data-bs-toggle="modal"
                                                data-bs-target="#history-modal-{{ $medicalHistory->id }}">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <a href="{{ route('medical-histories.edit', $medicalHistory) }}"
                                                class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form method="POST"
                                                action="{{ route('medical-histories.destroy', $medicalHistory) }}"
                                                class="d-inline"
                                                onsubmit="return confirm('Delete this medical history record?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Details modals (one per record, opened with the eye button) --}}
                    @foreach ($patient->medicalHistories as $medicalHistory)
                        <div class="modal fade" id="history-modal-{{ $medicalHistory->id }}" tabindex="-1"
                            aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">
                                            Visit on {{ $medicalHistory->visit_date->format('d M Y') }}
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <table class="table table-sm">
                                            <tr>
                                                <th class="w-25">Chief Complaint</th>
                                                <td>{{ $medicalHistory->chief_complaint }}</td>
                                            </tr>
                                            <tr>
                                                <th>Diagnosis</th>
                                                <td>{{ $medicalHistory->diagnosis ?? '—' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Previous Treatment</th>
                                                <td>{{ $medicalHistory->previous_treatment ?? '—' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Allergies</th>
                                                <td>{{ $medicalHistory->allergies ?? '—' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Medical Conditions</th>
                                                <td>{{ $medicalHistory->medical_conditions ?? '—' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Current Medications</th>
                                                <td>{{ $medicalHistory->current_medications ?? '—' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Notes</th>
                                                <td>{{ $medicalHistory->notes ?? '—' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Recorded By</th>
                                                <td>
                                                    {{ $medicalHistory->createdBy->name ?? '—' }}
                                                    on {{ $medicalHistory->created_at->format('d M Y') }}
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        @endif

        {{-- ================= APPOINTMENTS (filled in Phase 5) ================= --}}
        @if ($showAppointments)
            <div class="tab-pane fade" id="appointments" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">Appointments</h6>
                    <a href="{{ route('appointments.create', ['patient_id' => $patient->id]) }}"
                        class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-lg me-1"></i> Add Appointment
                    </a>
                </div>

                @if ($patient->appointments->isEmpty())
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-calendar-check fs-1 d-block mb-2"></i>
                        No appointments yet.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Date &amp; Time</th>
                                    <th>Treatment</th>
                                    <th>Status</th>
                                    <th>Notes</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($patient->appointments as $appointment)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $appointment->appointment_date->format('d M Y') }}
                                            </div>
                                            <div class="text-muted small">{{ $appointment->appointment_time }}</div>
                                        </td>
                                        <td>{{ $appointment->treatment->name ?? '—' }}</td>
                                        <td>
                                            <span class="badge text-bg-{{ $appointment->statusColor() }}">
                                                {{ $appointment->statusLabel() }}
                                            </span>
                                        </td>
                                        <td>{{ \Illuminate\Support\Str::limit($appointment->notes ?? '—', 30) }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('appointments.show', $appointment) }}"
                                                class="btn btn-sm btn-outline-secondary" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('appointments.edit', $appointment) }}"
                                                class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form method="POST"
                                                action="{{ route('appointments.destroy', ['appointment' => $appointment, 'redirect' => 'patient']) }}"
                                                class="d-inline" onsubmit="return confirm('Delete this appointment?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endif

        {{-- ================= TREATMENTS (filled in Phase 6) ================= --}}
        @if ($showTreatments)
            <div class="tab-pane fade" id="treatments" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">Treatment History</h6>
                    <a href="{{ route('patient-treatments.create', ['patient_id' => $patient->id]) }}"
                        class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-lg me-1"></i> Record Treatment
                    </a>
                </div>

                @if ($patient->patientTreatments->isEmpty())
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-bandaid fs-1 d-block mb-2"></i>
                        No treatment records yet.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Treatment</th>
                                    <th>Price</th>
                                    <th>Discount</th>
                                    <th>Total</th>
                                    <th>Notes</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($patient->patientTreatments as $patientTreatment)
                                    <tr>
                                        <td>{{ $patientTreatment->treatment_date->format('d M Y') }}</td>
                                        <td>{{ $patientTreatment->treatment->name ?? '—' }}</td>
                                        <td>{{ number_format($patientTreatment->price, 2) }}</td>
                                        <td>{{ number_format($patientTreatment->discount, 2) }}</td>
                                        <td class="fw-semibold">{{ number_format($patientTreatment->total_amount, 2) }}
                                        </td>
                                        <td>{{ \Illuminate\Support\Str::limit($patientTreatment->notes ?? '—', 25) }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('patient-treatments.edit', $patientTreatment) }}"
                                                class="btn btn-sm btn-outline-primary" title="Edit"><i
                                                    class="bi bi-pencil"></i></a>
                                            <form method="POST"
                                                action="{{ route('patient-treatments.destroy', ['patient_treatment' => $patientTreatment, 'redirect' => 'patient']) }}"
                                                class="d-inline"
                                                onsubmit="return confirm('Delete this treatment record?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    title="Delete"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endif

        {{-- ================= BILLING (filled in Phases 7 & 8) ================= --}}
        @if ($showBilling)
            <div class="tab-pane fade" id="billing" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">Invoices</h6>
                    <a href="{{ route('invoices.create', ['patient_id' => $patient->id]) }}"
                        class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-lg me-1"></i> Create Invoice
                    </a>
                </div>

                @if ($patient->invoices->isEmpty())
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-receipt fs-1 d-block mb-2"></i>
                        No invoices yet.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Date</th>
                                    <th>Total</th>
                                    <th>Paid</th>
                                    <th>Due</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($patient->invoices as $invoice)
                                    <tr>
                                        <td class="fw-semibold">{{ $invoice->invoice_number }}</td>
                                        <td>{{ $invoice->invoice_date->format('d M Y') }}</td>
                                        <td>{{ number_format($invoice->total_amount, 2) }}</td>
                                        <td>{{ number_format($invoice->paid_amount, 2) }}</td>
                                        <td class="{{ $invoice->due_amount > 0 ? 'text-danger fw-semibold' : '' }}">
                                            {{ number_format($invoice->due_amount, 2) }}
                                        </td>
                                        <td>
                                            <span class="badge text-bg-{{ $invoice->statusColor() }}">
                                                {{ $invoice->statusLabel() }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('invoices.show', $invoice) }}"
                                                class="btn btn-sm btn-outline-secondary" title="View"><i
                                                    class="bi bi-eye"></i></a>
                                            <a href="{{ route('invoices.print', $invoice) }}"
                                                class="btn btn-sm btn-outline-dark" title="Print"><i
                                                    class="bi bi-printer"></i></a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="text-muted small mt-2">
                        Full invoice management (edit, delete) is available from <strong>Billing &rarr; Invoices</strong>.
                        Payment recording arrives in Phase 8.
                    </div>
                @endif
                <h6 class="mt-4 mb-2">Payment History</h6>

                @if ($patient->payments->isEmpty())
                    <p class="text-muted">No payments recorded yet.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Invoice</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Received By</th>
                                    <th class="text-end">Receipt</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($patient->payments as $payment)
                                    <tr>
                                        <td>{{ $payment->payment_date->format('d M Y') }}</td>
                                        <td>{{ $payment->invoice->invoice_number }}</td>
                                        <td class="fw-semibold text-success">{{ number_format($payment->amount, 2) }}</td>
                                        <td>{{ $payment->methodLabel() }}</td>
                                        <td>{{ $payment->receivedBy->name ?? '—' }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('payments.print', $payment) }}"
                                                class="btn btn-sm btn-outline-dark" title="Print receipt"><i
                                                    class="bi bi-printer"></i></a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endif

        {{-- ================= MEDICINE / PRODUCT USAGE (filled in Phase 11) ================= --}}
        @if ($showUsage)
            <div class="tab-pane fade" id="usage" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">Medicine / Product Usage</h6>
                    <a href="{{ route('medicine-usages.create', ['patient_id' => $patient->id, 'redirect' => 'patient']) }}"
                        class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-lg me-1"></i> Record Usage
                    </a>
                </div>

                @if ($patient->medicineUsages->isEmpty())
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-capsule fs-1 d-block mb-2"></i>
                        No product usage records yet.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Treatment</th>
                                    <th>Product</th>
                                    <th>Qty</th>
                                    <th>Used By</th>
                                    <th>Notes</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($patient->medicineUsages as $usage)
                                    <tr>
                                        <td>{{ $usage->usage_date->format('d M Y') }}</td>
                                        <td>{{ $usage->treatment->name ?? '—' }}</td>
                                        <td>{{ $usage->medicine->name }}</td>
                                        <td class="fw-semibold">{{ $usage->quantity }}</td>
                                        <td>{{ $usage->usedBy->name ?? '—' }}</td>
                                        <td>{{ \Illuminate\Support\Str::limit($usage->notes ?? '—', 25) }}</td>
                                        <td class="text-end">
                                            <form method="POST"
                                                action="{{ route('medicine-usages.destroy', ['medicine_usage' => $usage, 'redirect' => 'patient']) }}"
                                                class="d-inline"
                                                onsubmit="return confirm('Delete this usage record? The product stock will be restored.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endif

    </div>

    {{-- Open the tab named in the URL hash, e.g. /patients/5#medical-history --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var hash = window.location.hash;
            if (hash) {
                var trigger = document.querySelector('.nav-tabs button[data-bs-target="' + hash + '"]');
                if (trigger) {
                    bootstrap.Tab.getOrCreateInstance(trigger).show();
                }
            }
        });
    </script>

@endsection
