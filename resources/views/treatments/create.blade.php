@extends('layouts.app')

@section('title', 'Add Treatment')

@section('content')

    <div class="card shadow-sm" style="max-width: 640px;">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Add Treatment</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('treatments.store') }}">
                @csrf
                @include('treatments._form', ['treatment' => null])
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Save Treatment</button>
                    <a href="{{ route('treatments.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

@endsection
