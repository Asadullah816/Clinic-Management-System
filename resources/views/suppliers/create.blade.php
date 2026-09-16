@extends('layouts.app')

@section('title', 'Add Supplier')

@section('content')

    <div class="card shadow-sm" style="max-width: 720px;">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Add Supplier</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('suppliers.store') }}">
                @csrf
                @include('suppliers._form', ['supplier' => null])
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Save Supplier</button>
                    <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

@endsection
