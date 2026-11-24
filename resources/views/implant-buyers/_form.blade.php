<div class="row g-3">
    <div class="col-md-6">
        <label for="doctor_name" class="form-label fw-semibold">Doctor / Surgeon Name <span class="text-danger">*</span></label>
        <input type="text" id="doctor_name" name="doctor_name"
            value="{{ old('doctor_name', $implantBuyer->doctor_name ?? '') }}"
            class="form-control @error('doctor_name') is-invalid @enderror" required
            placeholder="e.g. Dr. Zeeshan Tariq, Dr. Ayesha Malik">
        @error('doctor_name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="clinic_hospital_name" class="form-label fw-semibold">Clinic / Hospital / Center Name</label>
        <input type="text" id="clinic_hospital_name" name="clinic_hospital_name"
            value="{{ old('clinic_hospital_name', $implantBuyer->clinic_hospital_name ?? '') }}"
            class="form-control @error('clinic_hospital_name') is-invalid @enderror"
            placeholder="e.g. City Dental & Maxillofacial Hospital">
        @error('clinic_hospital_name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="phone" class="form-label fw-semibold">WhatsApp / Mobile Number</label>
        <div class="input-group">
            <span class="input-group-text bg-light text-muted"><i class="bi bi-whatsapp text-success"></i></span>
            <input type="text" id="phone" name="phone"
                value="{{ old('phone', $implantBuyer->phone ?? '') }}"
                class="form-control @error('phone') is-invalid @enderror"
                placeholder="e.g. 03001234567 or +923001234567">
        </div>
        <div class="form-text small">Used for sending 1-click WhatsApp payment &amp; outstanding balance reminders.</div>
        @error('phone')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="email" class="form-label fw-semibold">Email Address</label>
        <input type="email" id="email" name="email"
            value="{{ old('email', $implantBuyer->email ?? '') }}"
            class="form-control @error('email') is-invalid @enderror"
            placeholder="e.g. doctor@example.com">
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-8">
        <label for="address" class="form-label fw-semibold">Clinic Address / Location</label>
        <input type="text" id="address" name="address"
            value="{{ old('address', $implantBuyer->address ?? '') }}"
            class="form-control @error('address') is-invalid @enderror"
            placeholder="e.g. Suite 4, Dental Plaza, Saddar Road...">
        @error('address')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="status" class="form-label fw-semibold">Account Status</label>
        <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
            <option value="active" {{ old('status', $implantBuyer->status ?? 'active') === 'active' ? 'selected' : '' }}>Active (Allow Sales)</option>
            <option value="inactive" {{ old('status', $implantBuyer->status ?? '') === 'inactive' ? 'selected' : '' }}>Inactive / Suspended</option>
        </select>
        @error('status')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <div class="form-check p-3 rounded bg-light border">
            <input class="form-check-input ms-0 me-2" type="checkbox" name="is_internal" value="1" id="is_internal"
                {{ old('is_internal', $implantBuyer->is_internal ?? false) ? 'checked' : '' }}>
            <label class="form-check-label fw-semibold text-dark" for="is_internal">
                In-House Clinic Usage / Admin Self-Use
            </label>
            <div class="form-text small ps-4">
                Check this option if this entry represents our own in-house clinic surgeries by Dr Badshah Afsar (internal self-use).
            </div>
        </div>
    </div>

    <div class="col-12">
        <label for="notes" class="form-label fw-semibold">Internal Notes / Payment Terms</label>
        <textarea id="notes" name="notes" rows="2"
            class="form-control @error('notes') is-invalid @enderror"
            placeholder="e.g. Preferred delivery schedule, payment terms (15 days net), special discounts...">{{ old('notes', $implantBuyer->notes ?? '') }}</textarea>
        @error('notes')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
