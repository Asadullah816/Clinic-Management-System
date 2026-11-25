@extends('layouts.app')

@section('title', 'Edit ' . $failedImplant->failure_number)

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h4 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-pencil text-primary me-2"></i>Edit {{ $failedImplant->failure_number }}
                    </h4>
                    <p class="text-muted small mb-0">Update failure diagnosis, warranty resolution, or clinical details.</p>
                </div>
                <a href="{{ route('failed-implants.show', $failedImplant) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('failed-implants.update', $failedImplant) }}">
                        @csrf
                        @method('PUT')

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Failure Stage <span class="text-danger">*</span></label>
                                <select name="failure_stage" class="form-select @error('failure_stage') is-invalid @enderror" required>
                                    @foreach (\App\Models\FailedImplant::stages() as $key => $label)
                                        <option value="{{ $key }}" {{ old('failure_stage', $failedImplant->failure_stage) === $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('failure_stage') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Failure Reason <span class="text-danger">*</span></label>
                                <select name="failure_reason" class="form-select @error('failure_reason') is-invalid @enderror" required>
                                    @foreach (\App\Models\FailedImplant::reasons() as $key => $label)
                                        <option value="{{ $key }}" {{ old('failure_reason', $failedImplant->failure_reason) === $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('failure_reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tooth Number / Site</label>
                                <input type="text" name="tooth_number" class="form-control @error('tooth_number') is-invalid @enderror"
                                    value="{{ old('tooth_number', $failedImplant->tooth_number) }}">
                                @error('tooth_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Patient Name</label>
                                <input type="text" name="patient_name" class="form-control @error('patient_name') is-invalid @enderror"
                                    value="{{ old('patient_name', $failedImplant->patient_name) }}">
                                @error('patient_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Warranty / Settlement Status <span class="text-danger">*</span></label>
                                <select name="warranty_status" class="form-select @error('warranty_status') is-invalid @enderror" required>
                                    @foreach (\App\Models\FailedImplant::warrantyStatuses() as $key => $label)
                                        <option value="{{ $key }}" {{ old('warranty_status', $failedImplant->warranty_status) === $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('warranty_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Replacement Implant Provided</label>
                                <select name="replacement_implant_id" class="form-select @error('replacement_implant_id') is-invalid @enderror">
                                    <option value="">-- No Replacement Provided Yet --</option>
                                    @foreach ($implants as $rep)
                                        <option value="{{ $rep->id }}" {{ old('replacement_implant_id', $failedImplant->replacement_implant_id) == $rep->id ? 'selected' : '' }}>
                                            {{ $rep->fullSpecification() }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('replacement_implant_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Clinical Notes / Failure Description</label>
                            <textarea name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $failedImplant->notes) }}</textarea>
                            @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('failed-implants.show', $failedImplant) }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-check2-circle me-1"></i> Update Record
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
