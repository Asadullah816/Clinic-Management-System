@extends('layouts.app')

@section('title', 'Edit Supplier')

@section('content')

    <div class="card shadow-sm" style="max-width: 720px;">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Edit Supplier: {{ $supplier->name }}</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('suppliers.update', $supplier) }}">
                @csrf
                @method('PUT')
                @include('suppliers._form', ['supplier' => $supplier])
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Update
                        Supplier</button>
                    <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

@endsection
