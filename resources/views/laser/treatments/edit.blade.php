@extends('layouts.app')

@section('title', 'Edit ' . $treatment->name)

@section('content')
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card border shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 fw-bold" style="color: #0f172a;">Edit Laser Treatment</h5>
                        <p class="text-muted small mb-0">{{ $treatment->name }}</p>
                    </div>
                    <a href="{{ route('laser.treatments.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Back to Catalog
                    </a>
                </div>

                <div class="card-body p-4">
                    <form method="POST" action="{{ route('laser.treatments.update', $treatment) }}">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-8">
                                <label for="name" class="form-label fw-semibold">Procedure Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name', $treatment->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="code" class="form-label fw-semibold">Code / SKU</label>
                                <input type="text" name="code" id="code" class="form-control @error('code') is-invalid @enderror"
                                       value="{{ old('code', $treatment->code) }}">
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="body_area" class="form-label fw-semibold">Body Area</label>
                                <input type="text" name="body_area" id="body_area" list="bodyAreasList"
                                       class="form-control @error('body_area') is-invalid @enderror"
                                       value="{{ old('body_area', $treatment->body_area) }}">
                                <datalist id="bodyAreasList">
                                    @foreach ($bodyAreas as $area)
                                        <option value="{{ $area }}">
                                    @endforeach
                                </datalist>
                                @error('body_area')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="price" class="form-label fw-semibold">Session Price (PKR) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" name="price" id="price"
                                       class="form-control @error('price') is-invalid @enderror"
                                       value="{{ old('price', $treatment->price) }}" required>
                                @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="duration" class="form-label fw-semibold">Duration (Minutes)</label>
                                <input type="number" step="5" min="5" name="duration" id="duration"
                                       class="form-control @error('duration') is-invalid @enderror"
                                       value="{{ old('duration', $treatment->duration) }}">
                                @error('duration')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="description" class="form-label fw-semibold">Description / Clinical Guidelines</label>
                                <textarea name="description" id="description" rows="3"
                                          class="form-control @error('description') is-invalid @enderror">{{ old('description', $treatment->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="status" class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                                <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                    <option value="active" {{ old('status', $treatment->status) === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status', $treatment->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-4 border-top mt-4">
                            <a href="{{ route('laser.treatments.index') }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4 fw-semibold">
                                <i class="bi bi-check-lg me-1"></i> Update Treatment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
