@extends('layouts.app')

@section('title', 'Add Medical History')

@section('content')

    <div class="card shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Add Medical History</h5>
            <span class="badge text-bg-secondary">
                {{ $patient->full_name }} ({{ $patient->patient_number }})
            </span>
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('medical-histories.store', $patient) }}">
                @csrf

                @include('medical-histories._form', ['medicalHistory' => null, 'patient' => $patient])

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Save Record
                    </button>
                    <a href="{{ route('patients.show', $patient) }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

@endsection
