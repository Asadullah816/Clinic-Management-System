@extends('layouts.app')

@section('title', 'Add Product')

@section('content')

    <div class="card shadow-sm" style="max-width: 860px;">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Add Medicine / Product</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('medicines.store') }}">
                @csrf
                @include('medicines._form', [
                    'medicine' => null,
                    'categories' => $categories,
                    'suppliers' => $suppliers,
                ])
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Save Product</button>
                    <a href="{{ route('medicines.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

@endsection
