@extends('layouts.app')

@section('title', 'Payments')

@section('content')

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0">Payments</h5>
            <a href="{{ route('payments.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Receive Payment
            </a>
        </div>

        <div class="card-body">

            <form method="GET" action="{{ route('payments.index') }}" class="row g-2 mb-3">
                <div class="col-md-5">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                        placeholder="Search patient or invoice #...">
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
                <div class="col-md-4">
                    <button type="submit" class="btn btn-outline-secondary"><i class="bi bi-search me-1"></i>
                        Filter</button>
                    <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Patient</th>
                            <th>Invoice</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Received By</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payments as $payment)
                            <tr>
                                <td>{{ $payment->payment_date->format('d M Y') }}</td>
                                <td>
                                    <a href="{{ route('patients.show', $payment->patient) }}" class="text-decoration-none">
                                        {{ $payment->patient->full_name }}
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ route('invoices.show', $payment->invoice) }}" class="text-decoration-none">
                                        {{ $payment->invoice->invoice_number }}
                                    </a>
                                </td>
                                <td class="fw-semibold text-success">{{ number_format($payment->amount, 2) }}</td>
                                <td>{{ $payment->methodLabel() }}</td>
                                <td>{{ $payment->receivedBy->name ?? '—' }}</td>
                                <td class="text-end">
                                    <a href="{{ route('payments.print', $payment) }}" class="btn btn-sm btn-outline-dark"
                                        title="Print receipt"><i class="bi bi-printer"></i></a>
                                    <a href="{{ route('payments.edit', $payment) }}" class="btn btn-sm btn-outline-primary"
                                        title="Edit"><i class="bi bi-pencil"></i></a>
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
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No payments found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $payments->links() }}

        </div>
    </div>

@endsection
