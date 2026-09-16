@extends('layouts.app')

@section('title', 'Edit Category')

@section('content')

    <div class="card shadow-sm" style="max-width: 560px;">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Edit Category: {{ $category->name }}</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('medicine-categories.update', $category) }}">
                @csrf
                @method('PUT')
                @include('medicine-categories._form', ['category' => $category])
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Update
                        Category</button>
                    <a href="{{ route('medicine-categories.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

@endsection
