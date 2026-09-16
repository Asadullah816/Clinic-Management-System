@extends('layouts.app')

@section('title', 'Edit Invoice')

@section('content')

    <div class="card shadow-sm" style="max-width: 820px;">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Edit Invoice {{ $invoice->invoice_number }}</h5>
            <span class="badge text-bg-{{ $invoice->statusColor() }}">{{ $invoice->statusLabel() }}</span>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('invoices.update', $invoice) }}">
                @csrf
                @method('PUT')
                @include('invoices._form', [
                    'invoice' => $invoice,
                    'patients' => $patients,
                    'patientId' => null,
                ])
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Update
                        Invoice</button>
                    <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

@endsection
