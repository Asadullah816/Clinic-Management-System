@extends('layouts.app')

@section('title', 'Edit ' . $patient->full_name)

@section('content')
    <div class="row justify-content-center">
        <div class="col-12 col-lg-9">
            <div class="card border shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 fw-bold" style="color: #0f172a;">Edit Laser Patient Details</h5>
                        <p class="text-muted small mb-0">{{ $patient->patient_number }} — {{ $patient->full_name }}</p>
                    </div>
                    <a href="{{ route('laser.patients.show', $patient) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Back to Profile
                    </a>
                </div>

                <div class="card-body p-4">
                    <form method="POST" action="{{ route('laser.patients.update', $patient) }}">
                        @csrf
                        @method('PUT')

                        <h6 class="fw-bold text-primary mb-3"><i class="bi bi-person me-1"></i> Basic Demographics</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="first_name" class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                                <input type="text" name="first_name" id="first_name" class="form-control @error('first_name') is-invalid @enderror"
                                       value="{{ old('first_name', $patient->first_name) }}" required>
                                @error('first_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="last_name" class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                                <input type="text" name="last_name" id="last_name" class="form-control @error('last_name') is-invalid @enderror"
                                       value="{{ old('last_name', $patient->last_name) }}" required>
                                @error('last_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="gender" class="form-label fw-semibold">Gender <span class="text-danger">*</span></label>
                                <select name="gender" id="gender" class="form-select @error('gender') is-invalid @enderror" required>
                                    <option value="female" {{ old('gender', $patient->gender) === 'female' ? 'selected' : '' }}>Female</option>
                                    <option value="male" {{ old('gender', $patient->gender) === 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="other" {{ old('gender', $patient->gender) === 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('gender')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="date_of_birth" class="form-label fw-semibold">Date of Birth</label>
                                <input type="date" name="date_of_birth" id="date_of_birth" class="form-control @error('date_of_birth') is-invalid @enderror"
                                       value="{{ old('date_of_birth', $patient->date_of_birth?->format('Y-m-d')) }}">
                                @error('date_of_birth')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="phone" class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
                                <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror"
                                       value="{{ old('phone', $patient->phone) }}" required>
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label fw-semibold">Email Address</label>
                                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email', $patient->email) }}">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="emergency_contact" class="form-label fw-semibold">Emergency Contact</label>
                                <input type="text" name="emergency_contact" id="emergency_contact" class="form-control @error('emergency_contact') is-invalid @enderror"
                                       value="{{ old('emergency_contact', $patient->emergency_contact) }}">
                                @error('emergency_contact')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="address" class="form-label fw-semibold">Residential Address</label>
                                <input type="text" name="address" id="address" class="form-control @error('address') is-invalid @enderror"
                                       value="{{ old('address', $patient->address) }}">
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr class="my-4">

                        <h6 class="fw-bold text-primary mb-3"><i class="bi bi-shield-check me-1"></i> Laser Clinical Parameters &amp; History</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="skin_type" class="form-label fw-semibold">Fitzpatrick Skin Phototype</label>
                                <select name="skin_type" id="skin_type" class="form-select @error('skin_type') is-invalid @enderror">
                                    <option value="">Select Phototype...</option>
                                    @foreach ($fitzpatrickTypes as $key => $desc)
                                        <option value="{{ $key }}" {{ old('skin_type', $patient->skin_type) === $key ? 'selected' : '' }}>
                                            {{ $desc }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('skin_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="referred_by" class="form-label fw-semibold">Referred By</label>
                                <input type="text" name="referred_by" id="referred_by" class="form-control @error('referred_by') is-invalid @enderror"
                                       value="{{ old('referred_by', $patient->referred_by) }}">
                                @error('referred_by')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="medical_notes" class="form-label fw-semibold">Clinical Notes &amp; Contraindications</label>
                                <textarea name="medical_notes" id="medical_notes" rows="3"
                                          class="form-control @error('medical_notes') is-invalid @enderror">{{ old('medical_notes', $patient->medical_notes) }}</textarea>
                                @error('medical_notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="status" class="form-label fw-semibold">Patient Status <span class="text-danger">*</span></label>
                                <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                    <option value="active" {{ old('status', $patient->status) === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status', $patient->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                            <a href="{{ route('laser.patients.show', $patient) }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4 fw-semibold">
                                <i class="bi bi-check-lg me-1"></i> Update Patient Details
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
