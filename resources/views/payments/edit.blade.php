@extends('layouts.app')

@section('title', 'Edit Payment')

@section('content')

    <div class="card shadow-sm" style="max-width: 820px;">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Edit Payment {{ $payment->receiptNumber() }}</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('payments.update', $payment) }}">
                @csrf
                @method('PUT')
                @include('payments._form', [
                    'payment' => $payment,
                    'invoices' => $invoices,
                    'lockedInvoice' => $lockedInvoice,
                ])
                <div class="form-text mb-3">The invoice cannot be changed after creation.</div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Update
                        Payment</button>
                    <a href="{{ route('invoices.show', $payment->invoice) }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

@endsection
