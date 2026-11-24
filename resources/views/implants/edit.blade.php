@extends('layouts.app')

@section('title', 'Edit Implant - ' . $implant->name)

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0 fw-bold text-dark">
                <i class="bi bi-pencil-square text-primary me-2"></i>Edit {{ $implant->name }}
            </h4>
            <p class="text-muted small mb-0">{{ $implant->item_code }} &bull; {{ $implant->brand }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('implants.show', $implant) }}" class="btn btn-outline-secondary">
                <i class="bi bi-eye me-1"></i> View Details
            </a>
            <a href="{{ route('implants.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('implants.update', $implant) }}">
                @csrf
                @method('PUT')
                @include('implants._form')

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('implants.show', $implant) }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-check-lg me-1"></i> Update Implant
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
