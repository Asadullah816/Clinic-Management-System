@extends('layouts.app')

@section('title', 'Edit Patient')

@section('content')

    <div class="card shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Edit Patient: {{ $patient->full_name }}</h5>
            <span class="badge text-bg-secondary">{{ $patient->patient_number }}</span>
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('patients.update', $patient) }}">
                @csrf
                @method('PUT')

                @include('patients._form', ['patient' => $patient])

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Update Patient
                    </button>
                    <a href="{{ route('patients.show', $patient) }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

@endsection
