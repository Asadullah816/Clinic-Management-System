@extends('layouts.app')

@section('title', 'Edit Medical History')

@section('content')

    <div class="card shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Edit Medical History ({{ $medicalHistory->visit_date->format('d M Y') }})</h5>
            <span class="badge text-bg-secondary">
                {{ $patient->full_name }} ({{ $patient->patient_number }})
            </span>
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('medical-histories.update', $medicalHistory) }}">
                @csrf
                @method('PUT')

                @include('medical-histories._form', [
                    'medicalHistory' => $medicalHistory,
                    'patient' => $patient,
                ])

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Update Record
                    </button>
                    <a href="{{ route('patients.show', $patient) }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

@endsection
