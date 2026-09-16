@extends('layouts.app')

@section('title', 'Record Treatment')

@section('content')

    <div class="card shadow-sm" style="max-width: 820px;">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Record Patient Treatment</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('patient-treatments.store') }}">
                @csrf
                @include('patient-treatments._form', [
                    'patientTreatment' => null,
                    'patients' => $patients,
                    'treatments' => $treatments,
                    'patientId' => $patientId,
                    'treatmentId' => $treatmentId,
                    'price' => $price,
                ])
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Save Record</button>
                    <a href="{{ route('patient-treatments.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

@endsection
