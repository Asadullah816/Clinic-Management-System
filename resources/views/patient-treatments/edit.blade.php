@extends('layouts.app')

@section('title', 'Edit Treatment Record')

@section('content')

    <div class="card shadow-sm" style="max-width: 820px;">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Edit Treatment Record</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('patient-treatments.update', $patientTreatment) }}">
                @csrf
                @method('PUT')
                @include('patient-treatments._form', [
                    'patientTreatment' => $patientTreatment,
                    'patients' => $patients,
                    'treatments' => $treatments,
                    'patientId' => null,
                    'treatmentId' => null,
                    'price' => $patientTreatment->price,
                ])
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Update Record</button>
                    <a href="{{ route('patient-treatments.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

@endsection
