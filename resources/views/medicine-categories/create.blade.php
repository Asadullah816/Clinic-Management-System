@extends('layouts.app')

@section('title', 'Add Category')

@section('content')

    <div class="card shadow-sm" style="max-width: 560px;">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Add Medicine Category</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('medicine-categories.store') }}">
                @csrf
                @include('medicine-categories._form', ['category' => null])
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Save Category</button>
                    <a href="{{ route('medicine-categories.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

@endsection
