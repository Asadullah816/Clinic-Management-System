{{-- Expects: $appointment (null on create), $patients, $treatments, $patientId (optional preselect) --}}

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="patient_id" class="form-label">Patient <span class="text-danger">*</span></label>
        <select name="patient_id" id="patient_id" class="form-select @error('patient_id') is-invalid @enderror" required>
            <option value="">-- Select patient --</option>
            @foreach ($patients as $p)
                <option value="{{ $p->id }}"
                    {{ (string) old('patient_id', $appointment->patient_id ?? $patientId) === (string) $p->id ? 'selected' : '' }}>
                    {{ $p->patient_number }} &mdash; {{ $p->full_name }} ({{ $p->phone }})
                </option>
            @endforeach
        </select>
        @error('patient_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label for="treatment_id" class="form-label">Treatment</label>
        <select name="treatment_id" id="treatment_id" class="form-select @error('treatment_id') is-invalid @enderror">
            <option value="">-- No treatment selected --</option>
            @foreach ($treatments as $treatment)
                <option value="{{ $treatment->id }}"
                    {{ (string) old('treatment_id', $appointment->treatment_id ?? '') === (string) $treatment->id ? 'selected' : '' }}>
                    {{ $treatment->name }} ({{ number_format($treatment->price, 2) }})
                </option>
            @endforeach
        </select>
        <div class="form-text">Optional — leave empty for a general consultation.</div>
        @error('treatment_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label for="appointment_date" class="form-label">Date <span class="text-danger">*</span></label>
        <input type="date" id="appointment_date" name="appointment_date"
            value="{{ old('appointment_date', $appointment?->appointment_date?->format('Y-m-d')) }}"
            class="form-control @error('appointment_date') is-invalid @enderror" required>
        @error('appointment_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4 mb-3">
        <label for="appointment_time" class="form-label">Time <span class="text-danger">*</span></label>
        <input type="time" id="appointment_time" name="appointment_time"
            value="{{ old('appointment_time', $appointment->appointment_time ?? '') }}"
            class="form-control @error('appointment_time') is-invalid @enderror" required>
        @error('appointment_time')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4 mb-3">
        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
        <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
            @foreach (\App\Models\Appointment::statuses() as $statusValue => $statusLabel)
                <option value="{{ $statusValue }}"
                    {{ old('status', $appointment->status ?? 'scheduled') === $statusValue ? 'selected' : '' }}>
                    {{ $statusLabel }}
                </option>
            @endforeach
        </select>
        @error('status')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="mb-3">
    <label for="notes" class="form-label">Notes</label>
    <textarea id="notes" name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror"
        placeholder="e.g. Patient requested evening slot...">{{ old('notes', $appointment->notes ?? '') }}</textarea>
    @error('notes')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
