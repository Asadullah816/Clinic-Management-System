@extends('layouts.app')

@section('title', 'Edit Treatment')

@section('content')

    <div class="card shadow-sm" style="max-width: 640px;">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Edit Treatment: {{ $treatment->name }}</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('treatments.update', $treatment) }}">
                @csrf
                @method('PUT')
                @include('treatments._form', ['treatment' => $treatment])
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Update
                        Treatment</button>
                    <a href="{{ route('treatments.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

@endsection
