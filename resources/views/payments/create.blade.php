@extends('layouts.app')

@section('title', 'Receive Payment')

@section('content')

    <div class="card shadow-sm" style="max-width: 820px;">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Receive Payment</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('payments.store') }}">
                @csrf
                @include('payments._form', [
                    'payment' => $payment,
                    'invoices' => $invoices,
                    'lockedInvoice' => $lockedInvoice,
                ])
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Save Payment</button>
                    <a href="{{ $lockedInvoice ? route('invoices.show', $lockedInvoice) : route('payments.index') }}"
                        class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

@endsection
