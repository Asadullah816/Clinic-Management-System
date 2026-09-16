@extends('layouts.app')

@section('title', 'Edit Product')

@section('content')

    <div class="card shadow-sm" style="max-width: 860px;">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Edit Product: {{ $medicine->name }}</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('medicines.update', $medicine) }}">
                @csrf
                @method('PUT')
                @include('medicines._form', [
                    'medicine' => $medicine,
                    'categories' => $categories,
                    'suppliers' => $suppliers,
                ])
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Update Product</button>
                    <a href="{{ route('medicines.show', $medicine) }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

@endsection
