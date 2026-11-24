@extends('layouts.app')

@section('title', 'Register Doctor / Clinic')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0 fw-bold text-dark">
                <i class="bi bi-person-plus text-primary me-2"></i>Register Doctor / Clinic
            </h4>
            <p class="text-muted small mb-0">Add an external doctor or clinic purchasing dental implants from us.</p>
        </div>
        <a href="{{ route('implant-buyers.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Directory
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('implant-buyers.store') }}">
                @csrf
                @include('implant-buyers._form')

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('implant-buyers.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-check-lg me-1"></i> Save Doctor Profile
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
