@extends('layouts.app')

@section('title', 'Add Patient')

@section('content')

    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Add Patient</h5>
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('patients.store') }}">
                @csrf

                @include('patients._form', ['patient' => null])

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Save Patient
                    </button>
                    <a href="{{ route('patients.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

@endsection
