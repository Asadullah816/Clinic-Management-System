@extends('layouts.app')

@section('title', 'Record Expense')

@section('content')

<div class="card shadow-sm" style="max-width: 820px;">
    <div class="card-header bg-white py-3"><h5 class="mb-0">Record Expense</h5></div>
    <div class="card-body">
        <form method="POST" action="{{ route('expenses.store') }}">
            @csrf
            @include('expenses._form', ['expense' => null, 'categories' => $categories])
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Save Expense</button>
                <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

@endsection
