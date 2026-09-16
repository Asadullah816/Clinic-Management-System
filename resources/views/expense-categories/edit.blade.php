@extends('layouts.app')

@section('title', 'Edit Expense Category')

@section('content')

    <div class="card shadow-sm" style="max-width: 560px;">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Edit Expense Category: {{ $category->name }}</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('expense-categories.update', $category) }}">
                @csrf
                @method('PUT')
                @include('expense-categories._form', ['category' => $category])
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Update
                        Category</button>
                    <a href="{{ route('expense-categories.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

@endsection
