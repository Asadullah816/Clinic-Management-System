{{-- Expects: $patient (null on create, model on edit) --}}

<div class="mb-3">
    <label class="form-label fw-semibold">Personal Information</label>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
            <input type="text" id="first_name" name="first_name"
                value="{{ old('first_name', $patient->first_name ?? '') }}"
                class="form-control @error('first_name') is-invalid @enderror" required>
            @error('first_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-6 mb-3">
            <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
            <input type="text" id="last_name" name="last_name"
                value="{{ old('last_name', $patient->last_name ?? '') }}"
                class="form-control @error('last_name') is-invalid @enderror" required>
            @error('last_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4 mb-3">
            <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
            <select name="gender" id="gender" class="form-select @error('gender') is-invalid @enderror" required>
                <option value="">-- Select --</option>
                @foreach (['male', 'female', 'other'] as $gender)
                    <option value="{{ $gender }}"
                        {{ old('gender', $patient->gender ?? '') === $gender ? 'selected' : '' }}>
                        {{ ucfirst($gender) }}
                    </option>
                @endforeach
            </select>
            @error('gender')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4 mb-3">
            <label for="date_of_birth" class="form-label">Date of Birth</label>
            <input type="date" id="date_of_birth" name="date_of_birth"
                value="{{ old('date_of_birth', $patient?->date_of_birth?->format('Y-m-d')) }}"
                class="form-control @error('date_of_birth') is-invalid @enderror">
            @error('date_of_birth')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4 mb-3">
            <label for="occupation" class="form-label">Occupation</label>
            <input type="text" id="occupation" name="occupation"
                value="{{ old('occupation', $patient->occupation ?? '') }}"
                class="form-control @error('occupation') is-invalid @enderror">
            @error('occupation')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="mb-3">
    <label class="form-label fw-semibold">Contact Information</label>
    <div class="row">
        <div class="col-md-4 mb-3">
            <label for="phone" class="form-label">Phone <span class="text-danger">*</span></label>
            <input type="text" id="phone" name="phone" value="{{ old('phone', $patient->phone ?? '') }}"
                class="form-control @error('phone') is-invalid @enderror" required>
            @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4 mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email', $patient->email ?? '') }}"
                class="form-control @error('email') is-invalid @enderror">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4 mb-3">
            <label for="emergency_contact" class="form-label">Emergency Contact</label>
            <input type="text" id="emergency_contact" name="emergency_contact"
                value="{{ old('emergency_contact', $patient->emergency_contact ?? '') }}"
                class="form-control @error('emergency_contact') is-invalid @enderror">
            @error('emergency_contact')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-12 mb-3">
            <label for="address" class="form-label">Address</label>
            <textarea id="address" name="address" rows="2" class="form-control @error('address') is-invalid @enderror">{{ old('address', $patient->address ?? '') }}</textarea>
            @error('address')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="mb-3">
    <label class="form-label fw-semibold">Medical Information</label>
    <div class="row">
        <div class="col-md-4 mb-3">
            <label for="skin_type" class="form-label">Skin Type</label>
            <input type="text" id="skin_type" name="skin_type"
                value="{{ old('skin_type', $patient->skin_type ?? '') }}"
                class="form-control @error('skin_type') is-invalid @enderror"
                placeholder="e.g. Oily, Dry, Combination">
            @error('skin_type')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4 mb-3">
            <label for="referred_by" class="form-label">Referred By</label>
            <input type="text" id="referred_by" name="referred_by"
                value="{{ old('referred_by', $patient->referred_by ?? '') }}"
                class="form-control @error('referred_by') is-invalid @enderror">
            @error('referred_by')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4 mb-3">
            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror"
                required>
                <option value="active"
                    {{ old('status', $patient->status ?? 'active') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive"
                    {{ old('status', $patient->status ?? 'active') === 'inactive' ? 'selected' : '' }}>Inactive
                </option>
            </select>
            @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-6 mb-3">
            <label for="allergies" class="form-label">Allergies</label>
            <textarea id="allergies" name="allergies" rows="2"
                class="form-control @error('allergies') is-invalid @enderror">{{ old('allergies', $patient->allergies ?? '') }}</textarea>
            @error('allergies')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-6 mb-3">
            <label for="medical_notes" class="form-label">Medical Notes</label>
            <textarea id="medical_notes" name="medical_notes" rows="2"
                class="form-control @error('medical_notes') is-invalid @enderror">{{ old('medical_notes', $patient->medical_notes ?? '') }}</textarea>
            @error('medical_notes')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>
