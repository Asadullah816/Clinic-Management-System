@extends('layouts.app')

@section('title', 'Invoice ' . $invoice->invoice_number)

@section('content')

    <div class="card shadow-sm" style="max-width: 820px;">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Invoice {{ $invoice->invoice_number }}</h5>
            <span class="badge text-bg-{{ $invoice->statusColor() }} fs-6">{{ $invoice->statusLabel() }}</span>
        </div>

        <div class="card-body">

            <div class="row mb-3">
                <div class="col-md-6">
                    <h6 class="text-muted text-uppercase small">Billed To</h6>
                    <div class="fw-semibold">{{ $invoice->patient->full_name }}</div>
                    <div class="text-muted small">
                        {{ $invoice->patient->patient_number }}<br>
                        {{ $invoice->patient->phone }}<br>
                        {{ $invoice->patient->address ?? '' }}
                    </div>
                </div>
                <div class="col-md-6 text-md-end">
                    <h6 class="text-muted text-uppercase small">Details</h6>
                    <div class="small">
                        <div>Invoice Date: <strong>{{ $invoice->invoice_date->format('d M Y') }}</strong></div>
                        <div>Created By: {{ $invoice->createdBy->name ?? '—' }}</div>
                    </div>
                </div>
            </div>

            <table class="table table-sm table-bordered">
                <tr>
                    <th class="w-50">Subtotal</th>
                    <td class="text-end">{{ number_format($invoice->subtotal, 2) }}</td>
                </tr>
                <tr>
                    <th>Discount</th>
                    <td class="text-end">&minus; {{ number_format($invoice->discount, 2) }}</td>
                </tr>
                <tr class="table-light">
                    <th>Total Amount</th>
                    <td class="text-end fw-bold">{{ number_format($invoice->total_amount, 2) }}</td>
                </tr>
                <tr>
                    <th>Paid</th>
                    <td class="text-end text-success">{{ number_format($invoice->paid_amount, 2) }}</td>
                </tr>
                <tr>
                    <th>Due</th>
                    <td class="text-end {{ $invoice->due_amount > 0 ? 'text-danger fw-bold' : 'fw-bold' }}">
                        {{ number_format($invoice->due_amount, 2) }}
                    </td>
                </tr>
            </table>

            @if ($invoice->notes)
                <h6 class="text-muted text-uppercase small">Notes</h6>
                <p class="mb-3">{{ $invoice->notes }}</p>
            @endif

            <h6 class="text-muted text-uppercase small">Payments</h6>

            @if ($invoice->payments->isEmpty())
                <p class="text-muted">No payments recorded yet.</p>
            @else
                <div class="table-responsive mb-2">
                    <table class="table table-sm table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Reference</th>
                                <th>Received By</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($invoice->payments as $payment)
                                <tr>
                                    <td>{{ $payment->payment_date->format('d M Y') }}</td>
                                    <td class="fw-semibold text-success">{{ number_format($payment->amount, 2) }}</td>
                                    <td>{{ $payment->methodLabel() }}</td>
                                    <td>{{ $payment->reference ?? '—' }}</td>
                                    <td>{{ $payment->receivedBy->name ?? '—' }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('payments.print', $payment) }}"
                                            class="btn btn-sm btn-outline-dark" title="Print receipt"><i
                                                class="bi bi-printer"></i></a>
                                        <a href="{{ route('payments.edit', $payment) }}"
                                            class="btn btn-sm btn-outline-primary" title="Edit"><i
                                                class="bi bi-pencil"></i></a>
                                        <form method="POST" action="{{ route('payments.destroy', $payment) }}"
                                            class="d-inline"
                                            onsubmit="return confirm('Delete this payment? The invoice totals will be updated.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i
                                                    class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if ($invoice->due_amount > 0)
                <a href="{{ route('payments.create', ['invoice_id' => $invoice->id]) }}" class="btn btn-success mb-3">
                    <i class="bi bi-cash-coin me-1"></i> Receive Payment (Due:
                    {{ number_format($invoice->due_amount, 2) }})
                </a>
            @else
                <div class="alert alert-success py-2 mb-3">
                    <i class="bi bi-check-circle me-1"></i> This invoice is fully paid.
                </div>
            @endif

            <div class="d-flex gap-2">
                <a href="{{ route('invoices.print', $invoice) }}" class="btn btn-dark">
                    <i class="bi bi-printer me-1"></i> Print
                </a>
                <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-primary">
                    <i class="bi bi-pencil me-1"></i> Edit
                </a>
                <form method="POST" action="{{ route('invoices.destroy', $invoice) }}"
                    onsubmit="return confirm('Delete invoice {{ $invoice->invoice_number }}?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="bi bi-trash me-1"></i> Delete
                    </button>
                </form>
                <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary ms-auto">
                    <i class="bi bi-arrow-left me-1"></i> Back to Invoices
                </a>
            </div>
        </div>
    </div>

@endsection
