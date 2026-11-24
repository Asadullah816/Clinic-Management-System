@extends('layouts.app')

@section('title', 'Edit Doctor - ' . $implantBuyer->doctor_name)

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0 fw-bold text-dark">
                <i class="bi bi-pencil-square text-primary me-2"></i>Edit {{ $implantBuyer->doctor_name }}
            </h4>
            <p class="text-muted small mb-0">{{ $implantBuyer->clinic_hospital_name ?: 'Doctor Account' }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('implant-buyers.show', $implantBuyer) }}" class="btn btn-outline-secondary">
                <i class="bi bi-eye me-1"></i> View Profile
            </a>
            <a href="{{ route('implant-buyers.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('implant-buyers.update', $implantBuyer) }}">
                @csrf
                @method('PUT')
                @include('implant-buyers._form')

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('implant-buyers.show', $implantBuyer) }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-check-lg me-1"></i> Update Doctor Profile
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
