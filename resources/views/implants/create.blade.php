@extends('layouts.app')

@section('title', 'Add New Dental Implant')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0 fw-bold text-dark">
                <i class="bi bi-plus-circle text-primary me-2"></i>Add New Dental Implant
            </h4>
            <p class="text-muted small mb-0">Register a new implant fixture with brand, dimensions, and initial stock.</p>
        </div>
        <a href="{{ route('implants.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Inventory
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('implants.store') }}">
                @csrf
                @include('implants._form')

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('implants.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-check-lg me-1"></i> Save Implant
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
