@extends('layouts.app')

@section('title', 'Edit Appointment')

@section('content')

    <div class="card shadow-sm" style="max-width: 760px;">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Edit Appointment</h5>
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('appointments.update', $appointment) }}">
                @csrf
                @method('PUT')

                @include('appointments._form', [
                    'appointment' => $appointment,
                    'patients' => $patients,
                    'treatments' => $treatments,
                    'patientId' => null,
                ])

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Update Appointment
                    </button>
                    <a href="{{ route('appointments.show', $appointment) }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

@endsection
