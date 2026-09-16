@extends('layouts.app')

@section('title', 'Payment Report')

@section('content')

    <div class="card shadow-sm mb-3 border-start border-success border-4">
        <div class="card-body d-flex justify-content-between align-items-center py-3">
            <div>
                <div class="text-muted small">Total Received @if (request()->filled('from') || request()->filled('to') || request()->filled('payment_method'))
                        (for current filter)
                    @endif
                </div>
                <div class="fs-3 fw-bold text-success">{{ number_format($filteredTotal, 2) }}</div>
            </div>
            <i class="bi bi-cash-coin fs-1 text-success opacity-25"></i>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0">Payment Report</h5>
            <button type="button" class="btn btn-outline-dark btn-sm no-print" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print
            </button>
        </div>

        <div class="card-body">

            <form method="GET" action="{{ route('reports.payments') }}" class="row g-2 mb-3 no-print">
                <div class="col-md-3">
                    <input type="date" name="from" value="{{ request('from') }}" class="form-control"
                        title="From date">
                </div>
                <div class="col-md-3">
                    <input type="date" name="to" value="{{ request('to') }}" class="form-control" title="To date">
                </div>
                <div class="col-md-3">
                    <select name="payment_method" class="form-select">
                        <option value="">All methods</option>
                        @foreach (\App\Models\Payment::methods() as $methodValue => $methodLabel)
                            <option value="{{ $methodValue }}"
                                {{ request('payment_method') === $methodValue ? 'selected' : '' }}>
                                {{ $methodLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-outline-secondary"><i class="bi bi-search me-1"></i>
                        Filter</button>
                    <a href="{{ route('reports.payments') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Patient</th>
                            <th>Invoice</th>
                            <th>Method</th>
                            <th>Amount</th>
                            <th>Received By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payments as $payment)
                            <tr>
                                <td>{{ $payment->payment_date->format('d M Y') }}</td>
                                <td>{{ $payment->patient->full_name }}</td>
                                <td>{{ $payment->invoice->invoice_number }}</td>
                                <td>{{ $payment->methodLabel() }}</td>
                                <td class="fw-semibold text-success">{{ number_format($payment->amount, 2) }}</td>
                                <td>{{ $payment->receivedBy->name ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No payments found for this filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $payments->links() }}

        </div>
    </div>

    <style>
        @media print {

            .sidebar,
            .navbar,
            footer,
            .no-print {
                display: none !important;
            }

            .main-wrapper {
                margin-left: 0 !important;
            }

            body {
                background: #fff !important;
            }

            .card {
                border: none !important;
                box-shadow: none !important;
            }
        }
    </style>

@endsection
