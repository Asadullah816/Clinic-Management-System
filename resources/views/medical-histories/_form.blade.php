{{-- Expects: $patient (always set), $medicalHistory (null on create) --}}

<div class="row">
    <div class="col-md-4 mb-3">
        <label for="visit_date" class="form-label">Visit Date <span class="text-danger">*</span></label>
        <input type="date" id="visit_date" name="visit_date"
            value="{{ old('visit_date', $medicalHistory?->visit_date?->format('Y-m-d') ?? now()->toDateString()) }}"
            class="form-control @error('visit_date') is-invalid @enderror" required>
        @error('visit_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-8 mb-3">
        <label for="chief_complaint" class="form-label">Chief Complaint <span class="text-danger">*</span></label>
        <input type="text" id="chief_complaint" name="chief_complaint"
            value="{{ old('chief_complaint', $medicalHistory->chief_complaint ?? '') }}"
            class="form-control @error('chief_complaint') is-invalid @enderror"
            placeholder="e.g. Acne on cheeks, pigmentation, hair loss..." required>
        @error('chief_complaint')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="diagnosis" class="form-label">Diagnosis</label>
        <textarea id="diagnosis" name="diagnosis" rows="2" class="form-control @error('diagnosis') is-invalid @enderror">{{ old('diagnosis', $medicalHistory->diagnosis ?? '') }}</textarea>
        @error('diagnosis')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="previous_treatment" class="form-label">Previous Treatment</label>
        <textarea id="previous_treatment" name="previous_treatment" rows="2"
            class="form-control @error('previous_treatment') is-invalid @enderror">{{ old('previous_treatment', $medicalHistory->previous_treatment ?? '') }}</textarea>
        @error('previous_treatment')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label for="allergies" class="form-label">Allergies</label>
        <textarea id="allergies" name="allergies" rows="2" class="form-control @error('allergies') is-invalid @enderror">{{ old('allergies', $medicalHistory->allergies ?? '') }}</textarea>
        @error('allergies')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4 mb-3">
        <label for="medical_conditions" class="form-label">Medical Conditions</label>
        <textarea id="medical_conditions" name="medical_conditions" rows="2"
            class="form-control @error('medical_conditions') is-invalid @enderror" placeholder="e.g. Diabetes, hypertension...">{{ old('medical_conditions', $medicalHistory->medical_conditions ?? '') }}</textarea>
        @error('medical_conditions')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4 mb-3">
        <label for="current_medications" class="form-label">Current Medications</label>
        <textarea id="current_medications" name="current_medications" rows="2"
            class="form-control @error('current_medications') is-invalid @enderror">{{ old('current_medications', $medicalHistory->current_medications ?? '') }}</textarea>
        @error('current_medications')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="mb-3">
    <label for="notes" class="form-label">Notes</label>
    <textarea id="notes" name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $medicalHistory->notes ?? '') }}</textarea>
    @error('notes')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
