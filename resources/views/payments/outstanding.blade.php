@extends('layouts.app')

@section('title', 'Outstanding Payments')

@section('content')

    {{-- Top Action and Cross-Navigation Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-exclamation-circle text-danger me-2"></i>Outstanding Invoice Payments</h4>
            <div class="text-muted small">Current invoices with unpaid or partially paid balances</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('patient-histories.outstanding') }}" class="btn btn-outline-warning btn-sm">
                <i class="bi bi-clock-history me-1"></i> Pre-App Patient Outstandings
            </a>
            @if (auth()->user()->hasRole('admin', 'accountant', 'staff'))
                <a href="{{ route('implant-sales.outstanding') }}" class="btn btn-outline-info btn-sm">
                    <i class="bi bi-pin-angle me-1"></i> Doctor Implant Outstandings
                </a>
            @endif
        </div>
    </div>

    {{-- Summary card --}}
    <div class="card shadow-sm mb-3 border-start border-danger border-4">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <div class="text-muted small">Total Outstanding Amount</div>
                <div class="fs-3 fw-bold text-danger">PKR {{ number_format($totalOutstanding, 2) }}</div>
            </div>
            <i class="bi bi-exclamation-circle fs-1 text-danger opacity-25"></i>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Invoices with Outstanding Balance</h5>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice #</th>
                            <th>Date</th>
                            <th>Patient</th>
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
                                <td class="fw-semibold">{{ $invoice->invoice_number }}</td>
                                <td>{{ $invoice->invoice_date->format('d M Y') }}</td>
                                <td>
                                    <a href="{{ route('patients.show', $invoice->patient) }}" class="text-decoration-none">
                                        {{ $invoice->patient->full_name }}
                                    </a>
                                    <div class="text-muted small">{{ $invoice->patient->phone }}</div>
                                </td>
                                <td>PKR {{ number_format($invoice->total_amount, 2) }}</td>
                                <td>PKR {{ number_format($invoice->paid_amount, 2) }}</td>
                                <td class="text-danger fw-semibold">PKR {{ number_format($invoice->due_amount, 2) }}</td>
                                <td>
                                    <span
                                        class="badge text-bg-{{ $invoice->statusColor() }}">{{ $invoice->statusLabel() }}</span>
                                </td>
                                <td class="text-end">
                                    @if ($invoice->due_amount > 0 && $invoice->whatsappReminderUrl())
                                        <a href="{{ $invoice->whatsappReminderUrl() }}" target="_blank"
                                            class="btn btn-sm btn-success me-1" style="background-color: #25D366; border-color: #25D366;"
                                            title="Send WhatsApp Reminder">
                                            <i class="bi bi-whatsapp me-1"></i> WhatsApp
                                        </a>
                                    @endif
                                    <a href="{{ route('payments.create', ['invoice_id' => $invoice->id]) }}"
                                        class="btn btn-sm btn-success" title="Receive payment">
                                        <i class="bi bi-cash-coin me-1"></i> Receive
                                    </a>
                                    <a href="{{ route('invoices.show', $invoice) }}"
                                        class="btn btn-sm btn-outline-secondary" title="View invoice"><i
                                            class="bi bi-eye"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-success py-4">
                                    <i class="bi bi-check-circle fs-1 d-block mb-2"></i>
                                    No outstanding balances — every invoice is fully paid.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $invoices->links() }}

        </div>
    </div>

@endsection
