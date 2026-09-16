@extends('layouts.app')

@section('title', 'Add Appointment')

@section('content')

    <div class="card shadow-sm" style="max-width: 760px;">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Add Appointment</h5>
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('appointments.store') }}">
                @csrf

                @include('appointments._form', [
                    'appointment' => null,
                    'patients' => $patients,
                    'treatments' => $treatments,
                    'patientId' => $patientId,
                ])

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Save Appointment
                    </button>
                    <a href="{{ route('appointments.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

@endsection
