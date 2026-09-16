@extends('layouts.app')

@section('title', 'Add Expense Category')

@section('content')

    <div class="card shadow-sm" style="max-width: 560px;">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Add Expense Category</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('expense-categories.store') }}">
                @csrf
                @include('expense-categories._form', ['category' => null])
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Save Category</button>
                    <a href="{{ route('expense-categories.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

@endsection
