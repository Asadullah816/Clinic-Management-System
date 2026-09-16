@extends('layouts.app')

@section('title', 'Outstanding Payments')

@section('content')

    {{-- Summary card --}}
    <div class="card shadow-sm mb-3 border-start border-danger border-4">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <div class="text-muted small">Total Outstanding Amount</div>
                <div class="fs-3 fw-bold text-danger">{{ number_format($totalOutstanding, 2) }}</div>
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
                            <th>Total</th>
                            <th>Paid</th>
                            <th>Due</th>
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
                                <td>{{ number_format($invoice->total_amount, 2) }}</td>
                                <td>{{ number_format($invoice->paid_amount, 2) }}</td>
                                <td class="text-danger fw-semibold">{{ number_format($invoice->due_amount, 2) }}</td>
                                <td>
                                    <span
                                        class="badge text-bg-{{ $invoice->statusColor() }}">{{ $invoice->statusLabel() }}</span>
                                </td>
                                <td class="text-end">
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
