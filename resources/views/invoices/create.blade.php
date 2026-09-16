@extends('layouts.app')

@section('title', 'New Invoice')

@section('content')

    <div class="card shadow-sm" style="max-width: 820px;">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">New Invoice</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('invoices.store') }}">
                @csrf
                @include('invoices._form', [
                    'invoice' => null,
                    'patients' => $patients,
                    'patientId' => $patientId,
                ])
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Save Invoice</button>
                    <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

@endsection
