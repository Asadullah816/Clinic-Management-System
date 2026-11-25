@extends('layouts.app')

@section('title', 'Outstanding Dues - Patients')

@section('content')

    {{-- Top Action and Cross-Navigation Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h4 class="fw-bold mb-0 text-dark">
                <i class="bi bi-exclamation-circle text-danger me-2"></i>Outstanding Dues
            </h4>
            <div class="text-muted small">
                All patient treatment invoices with unpaid or partially paid balances.
            </div>
        </div>
        <div class="d-flex gap-2">
            @if (auth()->user()->hasRole('admin', 'accountant', 'staff'))
                <a href="{{ route('implant-sales.outstanding') }}" class="btn btn-outline-info btn-sm">
                    <i class="bi bi-pin-angle me-1"></i> Doctor Implant Outstandings
                </a>
            @endif
        </div>
    </div>

    {{-- Financial Overview Metrics Card --}}
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100 border-start border-danger border-4">
                <div class="card-body p-3">
                    <div class="text-muted small text-uppercase fw-semibold" style="font-size: 0.72rem;">Total Outstanding Dues</div>
                    <div class="fs-3 fw-bold text-danger mt-1">PKR {{ number_format($totalOutstanding, 2) }}</div>
                    <div class="small text-muted mt-1">{{ number_format($totalInvoicesCount) }} invoice(s) pending payment</div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100 border-start border-primary border-4">
                <div class="card-body p-3">
                    <div class="text-muted small text-uppercase fw-semibold" style="font-size: 0.72rem;">Pending Invoices Count</div>
                    <div class="fs-3 fw-bold text-dark mt-1">{{ number_format($totalInvoicesCount) }}</div>
                    <div class="small text-muted mt-1">Total unsettled treatment accounts</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Outstanding Table Card --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-hourglass-split text-danger me-1"></i> Patient Outstanding Invoices
                </h5>
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1 rounded-pill">
                    {{ $totalInvoicesCount }} Pending Invoices
                </span>
            </div>
        </div>

        <div class="card-body">

            {{-- Filter & Search Bar --}}
            <form method="GET" action="{{ route('payments.outstanding') }}" class="row g-2 mb-3">
                <div class="{{ ! auth()->user()->isDoctor() ? 'col-md-6' : 'col-md-9' }}">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm"
                        placeholder="Search patient name, MR#, phone, invoice #...">
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
                <div class="{{ ! auth()->user()->isDoctor() ? 'col-md-2' : 'col-md-3' }} d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-search me-1"></i> Filter
                    </button>
                    @if (request()->hasAny(['search', 'doctor_id']))
                        <a href="{{ route('payments.outstanding') }}" class="btn btn-outline-secondary btn-sm" title="Reset Filters">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    @endif
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice #</th>
                            <th>Date</th>
                            <th>Patient</th>
                            <th>Attending Doctor</th>
                            <th>Total (PKR)</th>
                            <th>Paid (PKR)</th>
                            <th>Due (PKR)</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($invoices as $invoice)
                            <tr>
                                <td class="fw-semibold">
                                    <a href="{{ route('invoices.show', $invoice) }}" class="text-dark text-decoration-none">
                                        {{ $invoice->invoice_number }}
                                    </a>
                                </td>
                                <td>{{ $invoice->invoice_date->format('d M Y') }}</td>
                                <td>
                                    @if ($invoice->patient)
                                        <a href="{{ route('patients.show', $invoice->patient) }}" class="fw-bold text-decoration-none text-primary">
                                            {{ $invoice->patient->full_name }}
                                        </a>
                                        <div class="small text-muted">
                                            <span class="badge bg-light text-secondary border me-1">{{ $invoice->patient->patient_number }}</span>
                                            <i class="bi bi-telephone me-1"></i>{{ $invoice->patient->phone ?: 'No phone' }}
                                        </div>
                                    @else
                                        <span class="text-muted">Unlinked Patient</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <i class="bi bi-person-badge text-primary me-1"></i>
                                        {{ $invoice->patient?->assignedDoctor?->name ?? 'Clinic Specialist' }}
                                    </span>
                                </td>
                                <td>{{ number_format($invoice->total_amount, 2) }}</td>
                                <td class="text-success">{{ number_format($invoice->paid_amount, 2) }}</td>
                                <td class="text-danger fw-bold fs-6">
                                    PKR {{ number_format($invoice->due_amount, 2) }}
                                </td>
                                <td>
                                    <span class="badge text-bg-{{ $invoice->statusColor() }}">
                                        {{ $invoice->statusLabel() }}
                                    </span>
                                </td>
                                <td class="text-end text-nowrap">
                                    @if ($invoice->due_amount > 0 && $invoice->whatsappReminderUrl())
                                        <a href="{{ $invoice->whatsappReminderUrl() }}" target="_blank"
                                            class="btn btn-sm btn-success py-1 px-2 me-1" style="background-color: #25D366; border-color: #25D366;"
                                            title="Send WhatsApp Payment Reminder">
                                            <i class="bi bi-whatsapp me-1"></i> Reminder
                                        </a>
                                    @endif
                                    <a href="{{ route('payments.create', ['invoice_id' => $invoice->id]) }}"
                                        class="btn btn-sm btn-primary py-1 px-2 me-1" title="Receive payment against invoice">
                                        <i class="bi bi-cash-coin me-1"></i> Receive
                                    </a>
                                    <a href="{{ route('invoices.show', $invoice) }}"
                                        class="btn btn-sm btn-outline-secondary py-1 px-2" title="View invoice">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-5">
                                    <i class="bi bi-check-circle-fill text-success fs-2 d-block mb-2"></i>
                                    <strong>No outstanding balances found!</strong>
                                    <div class="small text-muted mt-1">Every patient invoice is fully paid.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($invoices->hasPages())
                <div class="mt-3">
                    {{ $invoices->links() }}
                </div>
            @endif

        </div>
    </div>

@endsection
