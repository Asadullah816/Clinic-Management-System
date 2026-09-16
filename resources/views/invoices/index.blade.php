@extends('layouts.app')

@section('title', 'Invoices')

@section('content')

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0">Invoices</h5>
            <a href="{{ route('invoices.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> New Invoice
            </a>
        </div>

        <div class="card-body">

            <form method="GET" action="{{ route('invoices.index') }}" class="row g-2 mb-3">
                <div class="col-md-5">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                        placeholder="Search invoice # or patient...">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All statuses</option>
                        @foreach (\App\Models\Invoice::statuses() as $statusValue => $statusLabel)
                            <option value="{{ $statusValue }}" {{ request('status') === $statusValue ? 'selected' : '' }}>
                                {{ $statusLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-outline-secondary"><i class="bi bi-search me-1"></i>
                        Filter</button>
                    <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>

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
                                </td>
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
                                    <a href="{{ route('invoices.print', $invoice) }}" class="btn btn-sm btn-outline-dark"
                                        title="Print"><i class="bi bi-printer"></i></a>
                                    <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-sm btn-outline-primary"
                                        title="Edit"><i class="bi bi-pencil"></i></a>
                                    <form method="POST" action="{{ route('invoices.destroy', $invoice) }}"
                                        class="d-inline"
                                        onsubmit="return confirm('Delete invoice {{ $invoice->invoice_number }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i
                                                class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">No invoices found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $invoices->links() }}

        </div>
    </div>

@endsection
