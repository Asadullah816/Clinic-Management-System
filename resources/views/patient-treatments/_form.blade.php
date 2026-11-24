{{-- Expects: $patientTreatment (null on create), $patients, $treatments, $medicines, $patientId, $treatmentId, $appointmentId, $price, $patientAppointments --}}

@php
    $patientAppointments = $patientAppointments ?? [];
    $appointmentId = $appointmentId ?? null;

    // Resolve initial patient ID
    $currentPatientId = old('patient_id', $patientTreatment->patient_id ?? $patientId);
    $selectedPatient = $currentPatientId ? collect($patients)->firstWhere('id', (int) $currentPatientId) : null;
    $selectedPatientText = $selectedPatient
        ? ($selectedPatient->patient_number . ' — ' . $selectedPatient->full_name . ($selectedPatient->phone ? ' (' . $selectedPatient->phone . ')' : ''))
        : '';

    // Check if initial patient has a booked appointment
    $initialAppt = ($currentPatientId && isset($patientAppointments[(int) $currentPatientId]))
        ? $patientAppointments[(int) $currentPatientId]
        : null;

    // Initial procedures list
    $initialProcedures = [];
    if (old('procedures')) {
        foreach (old('procedures') as $p) {
            $tId = !empty($p['treatment_id']) ? (int) $p['treatment_id'] : null;
            $initialProcedures[] = [
                'treatment_id' => $tId,
                'name'         => $p['name'] ?? '',
                'price'        => (float) ($p['price'] ?? 0),
                'notes'        => $p['notes'] ?? '',
            ];
        }
    } elseif (isset($patientTreatment) && $patientTreatment) {
        $initialProcedures[] = [
            'treatment_id' => $patientTreatment->treatment_id,
            'name'         => $patientTreatment->procedure_name ?: ($patientTreatment->treatment?->name ?? 'Dental Procedure'),
            'price'        => (float) $patientTreatment->price,
            'notes'        => '',
        ];
    } elseif ($treatmentId) {
        $matchedTreatment = collect($treatments)->firstWhere('id', (int) $treatmentId);
        $initialProcedures[] = [
            'treatment_id' => (int) $treatmentId,
            'name'         => $matchedTreatment ? $matchedTreatment->name : '',
            'price'        => (float) ($price ?? ($matchedTreatment?->price ?? 0)),
            'notes'        => '',
        ];
    } elseif ($initialAppt && !empty($initialAppt['treatments']) && count($initialAppt['treatments']) > 0) {
        foreach ($initialAppt['treatments'] as $t) {
            $initialProcedures[] = [
                'treatment_id' => (int) $t['treatment_id'],
                'name'         => $t['name'],
                'price'        => (float) $t['price'],
                'notes'        => '',
            ];
        }
    } elseif ($initialAppt && !empty($initialAppt['treatment_id'])) {
        $initialProcedures[] = [
            'treatment_id' => (int) $initialAppt['treatment_id'],
            'name'         => $initialAppt['treatment_name'],
            'price'        => (float) $initialAppt['treatment_price'],
            'notes'        => '',
        ];
    } else {
        $initialProcedures[] = [
            'treatment_id' => null,
            'name'         => '',
            'price'        => (float) ($price ?? 0),
            'notes'        => '',
        ];
    }

@endphp

{{-- ================= ROW: PATIENT SEARCH DROPDOWN & TREATMENT DATE ================= --}}
<div class="row mb-3">
    <!-- Searchable Patient Dropdown -->
    <div class="col-md-5 mb-3 mb-md-0 position-relative" id="patient-search-wrapper">
        <label for="patient_search_input" class="form-label fw-semibold">
            Patient <span class="text-danger">*</span>
            <span class="text-muted fw-normal small ms-1">(Instant search)</span>
        </label>
        <div class="input-group">
            <span class="input-group-text bg-white text-primary"><i class="bi bi-person-search"></i></span>
            <input type="text" id="patient_search_input" class="form-control @error('patient_id') is-invalid @enderror"
                placeholder="Type patient name, MR # (e.g. P-0001), or phone..."
                autocomplete="off" value="{{ $selectedPatientText }}">
            <button class="btn btn-outline-secondary" type="button" id="clear_patient_btn"
                style="{{ $selectedPatient ? '' : 'display:none;' }}" title="Change or clear patient">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <input type="hidden" name="patient_id" id="patient_id" value="{{ $currentPatientId }}" required>
        <input type="hidden" name="appointment_id" id="appointment_id" value="{{ old('appointment_id', $appointmentId ?? ($initialAppt['appointment_id'] ?? '')) }}">

        @error('patient_id')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror

        <!-- Dynamic Patient Search Results Menu -->
        <div id="patient_search_dropdown" class="list-group position-absolute w-100 shadow-lg rounded-3 border mt-1"
            style="max-height: 320px; overflow-y: auto; z-index: 1060; display: none; background: #ffffff;">
        </div>
    </div>

    <!-- Attending Doctor Selection -->
    <div class="col-md-4 mb-3 mb-md-0">
        <label for="doctor_id" class="form-label fw-semibold">Attending Doctor</label>
        @if (auth()->user()->isDoctor())
            <input type="hidden" name="doctor_id" value="{{ auth()->id() }}">
            <div class="input-group">
                <span class="input-group-text bg-white text-primary"><i class="bi bi-person-badge"></i></span>
                <input type="text" class="form-control bg-light fw-semibold" value="{{ auth()->user()->name }} (You)" readonly>
            </div>
        @else
            <div class="input-group">
                <span class="input-group-text bg-white text-primary"><i class="bi bi-person-badge"></i></span>
                <select name="doctor_id" id="doctor_id" class="form-select @error('doctor_id') is-invalid @enderror">
                    <option value="">-- General / Assign Doctor --</option>
                    @if (isset($doctors))
                        @foreach ($doctors as $doc)
                            <option value="{{ $doc->id }}"
                                {{ (string) old('doctor_id', $patientTreatment->doctor_id ?? '') === (string) $doc->id ? 'selected' : '' }}>
                                {{ $doc->name }} ({{ $doc->role === 'admin' ? 'Super Admin / Lead' : 'Doctor' }})
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>
        @endif
        @error('doctor_id')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <!-- Treatment Date -->
    <div class="col-md-3">
        <label for="treatment_date" class="form-label fw-semibold">Treatment Date <span class="text-danger">*</span></label>
        <input type="date" id="treatment_date" name="treatment_date"
            value="{{ old('treatment_date', $patientTreatment?->treatment_date?->format('Y-m-d') ?? now()->toDateString()) }}"
            class="form-control @error('treatment_date') is-invalid @enderror" required>
        @error('treatment_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

{{-- ================= BOOKED APPOINTMENT CALLOUT BANNER ================= --}}
<div id="appointment_callout_banner" class="alert alert-success d-flex align-items-center justify-content-between p-3 mb-4 rounded-3 border border-success-subtle shadow-sm"
    style="{{ $initialAppt ? '' : 'display:none;' }}">
    <div class="d-flex align-items-center">
        <div class="bg-success text-white rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
            <i class="bi bi-calendar2-check-fill fs-5"></i>
        </div>
        <div>
            <div class="fw-bold text-success" id="banner_appt_title">
                📅 Booked Appointment: {{ $initialAppt['treatment_name'] ?? 'Scheduled Appointment' }}
            </div>
            <div class="small text-muted" id="banner_appt_subtitle">
                Scheduled for <span class="fw-semibold text-dark">{{ $initialAppt['appointment_date'] ?? '' }} ({{ $initialAppt['appointment_time'] ?? '' }})</span>. Procedure and catalog price automatically pre-selected below.
            </div>
        </div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">Auto-Selected</span>
    </div>
</div>

{{-- ================= SECTION 1: PROCEDURES & PRICING (RANDOM / CUSTOM / MULTIPLE) ================= --}}
<div class="card mb-4 border shadow-sm">
    <div class="card-header bg-primary-subtle py-2 d-flex justify-content-between align-items-center">
        <span class="fw-bold text-primary">
            <i class="bi bi-activity me-1"></i> 1. Treatment Procedures &amp; Clinical Services
        </span>
        <button type="button" class="btn btn-sm btn-primary py-1 px-3 shadow-sm" id="btn-add-procedure">
            <i class="bi bi-plus-lg me-1"></i> Add Another Procedure
        </button>
    </div>
    <div class="card-body">
        <div class="text-muted small mb-3">
            <i class="bi bi-info-circle me-1"></i>
            Select any dental treatment from catalog or enter a custom walk-in procedure. Multiple procedures performed in this visit can be added together.
        </div>

        @error('treatment_id')
            <div class="alert alert-danger py-2 mb-3">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $message }}
            </div>
        @enderror
        @error('procedure_name')
            <div class="alert alert-danger py-2 mb-3">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $message }}
            </div>
        @enderror

        <!-- Dynamic Procedures Container -->
        <div id="procedures-container" class="mb-3 d-flex flex-column gap-3">
            <!-- Populated via JavaScript -->
        </div>

        <!-- Hidden legacy/primary treatment_id & procedure_name for fallback compatibility -->
        <input type="hidden" name="treatment_id" id="treatment_id" value="{{ old('treatment_id', $patientTreatment->treatment_id ?? $treatmentId) }}">
        <input type="hidden" name="procedure_name" id="procedure_name" value="{{ old('procedure_name', $patientTreatment->procedure_name ?? '') }}">

        <!-- Procedure Fee, Discount, and Net Calculation -->
        <div class="bg-light p-3 rounded border">
            <div class="row align-items-center">
                <div class="col-md-4 mb-3 mb-md-0">
                    <label for="price" class="form-label fw-semibold">Total Procedure Fee (PKR) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-white">PKR</span>
                        <input type="number" step="0.01" min="0" id="price" name="price"
                            value="{{ old('price', $price ?? 0) }}" class="form-control @error('price') is-invalid @enderror" required>
                    </div>
                    <div class="form-text small">Automatically sums procedures. Editable per patient.</div>
                    @error('price')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4 mb-3 mb-md-0">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label fw-semibold mb-0">Procedure Discount</label>
                        <div class="btn-group btn-group-sm" role="group" aria-label="Procedure Discount Mode">
                            <input type="radio" class="btn-check" name="discount_type" id="dt_type_fixed" value="fixed"
                                {{ old('discount_type', 'fixed') === 'fixed' ? 'checked' : '' }} autocomplete="off">
                            <label class="btn btn-outline-primary py-0 px-2 small" for="dt_type_fixed">Fixed (PKR)</label>

                            <input type="radio" class="btn-check" name="discount_type" id="dt_type_percentage" value="percentage"
                                {{ old('discount_type') === 'percentage' ? 'checked' : '' }} autocomplete="off">
                            <label class="btn btn-outline-primary py-0 px-2 small" for="dt_type_percentage">% Off</label>
                        </div>
                    </div>

                    <!-- Fixed Price Discount Input -->
                    <div id="wrapper_discount_fixed">
                        <div class="input-group">
                            <span class="input-group-text bg-white">PKR</span>
                            <input type="number" step="0.01" min="0" id="discount" name="discount"
                                value="{{ old('discount', $patientTreatment->discount ?? 0) }}"
                                class="form-control @error('discount') is-invalid @enderror" placeholder="0.00">
                        </div>
                    </div>

                    <!-- Percentage Discount Input -->
                    <div id="wrapper_discount_percentage" style="display: none;">
                        <div class="input-group">
                            <input type="number" step="0.01" min="0" max="100" id="discount_percentage" name="discount_percentage"
                                value="{{ old('discount_percentage') }}"
                                class="form-control @error('discount_percentage') is-invalid @enderror" placeholder="0 - 100">
                            <span class="input-group-text bg-white">%</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <span class="badge bg-light text-primary border" id="dt_calc_badge">= PKR 0.00 off</span>
                            <span class="text-muted small">0% &ndash; 100%</span>
                        </div>
                    </div>

                    <div class="form-text small">Discount strictly applies to procedure fees.</div>
                    @error('discount')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    @error('discount_percentage')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold text-secondary">Procedure Net Total</label>
                    <div class="p-2 border rounded bg-white d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Fee &minus; Discount:</span>
                        <span class="fs-5 fw-bold text-primary" id="treatment-net-preview">PKR 0.00</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ================= SECTION 2: BILLING & GRAND TOTAL SUMMARY ================= --}}
<div class="card mb-4 border shadow-sm">
    <div class="card-header bg-dark text-white py-2 d-flex justify-content-between align-items-center">
        <span class="fw-bold">
            <i class="bi bi-receipt me-1"></i> 2. Grand Total &amp; Billing Summary
        </span>
        <span class="badge bg-light text-dark">Invoice Settlement</span>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-12">
                <div class="border rounded-3 p-3 bg-primary text-white text-center d-flex flex-column justify-content-center">
                    <span class="text-white-50 text-uppercase fw-semibold small tracking-wide">Grand Total Payable</span>
                    <div class="display-6 fw-bold my-1" id="grand-total-preview">PKR 0.00</div>
                    <div class="small text-white-50">Procedure Net Total</div>
                </div>
            </div>
        </div>

        @if (! $patientTreatment)
            <div class="bg-light p-3 rounded border">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="card-subtitle text-primary fw-semibold mb-0">
                        <i class="bi bi-credit-card me-1"></i> Immediate Payment Settlement
                    </h6>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-success btn-sm py-0 px-2" id="btn-full-paid">
                            <i class="bi bi-check2-all me-1"></i> Full Paid
                        </button>
                        <button type="button" class="btn btn-outline-warning btn-sm py-0 px-2 text-dark" id="btn-full-due">
                            <i class="bi bi-clock-history me-1"></i> Full Due
                        </button>
                    </div>
                </div>

                <div class="row align-items-start">
                    <div class="col-md-4 mb-3 mb-md-0">
                        <label for="paid_amount" class="form-label small fw-semibold text-secondary">
                            Paid Amount (PKR) <span class="text-danger">*</span>
                        </label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white">PKR</span>
                            <input type="number" step="0.01" min="0" id="paid_amount" name="paid_amount"
                                value="{{ old('paid_amount') }}"
                                placeholder="0.00"
                                class="form-control @error('paid_amount') is-invalid @enderror">
                        </div>
                        <div class="form-text small" id="paid-hint">Defaults to grand total. Edit for partial/due.</div>
                        @error('paid_amount')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3 mb-md-0">
                        <label class="form-label small fw-semibold text-secondary">Remaining Due</label>
                        <div class="form-control form-control-sm bg-white d-flex justify-content-between align-items-center">
                            <span class="fw-bold" id="due-preview">PKR 0.00</span>
                            <span id="payment-status-badge" class="badge bg-success">Paid</span>
                        </div>
                        <div class="form-text small" id="due-hint">No remaining balance.</div>
                    </div>

                    <div class="col-md-4">
                        <label for="payment_method" class="form-label small fw-semibold text-secondary">Payment Method</label>
                        <select name="payment_method" id="payment_method" class="form-select form-select-sm @error('payment_method') is-invalid @enderror">
                            @foreach (\App\Models\Payment::methods() as $key => $label)
                                <option value="{{ $key }}" {{ old('payment_method', 'cash') === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('payment_method')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-md-12">
                        <label for="payment_reference" class="form-label small fw-semibold text-secondary mb-1">
                            Payment Reference / Trx ID <span class="text-muted small">(optional)</span>
                        </label>
                        <input type="text" name="payment_reference" id="payment_reference"
                            value="{{ old('payment_reference') }}"
                            placeholder="e.g. Cash receipt, Card auth code, Bank Trx #"
                            class="form-control form-control-sm @error('payment_reference') is-invalid @enderror">
                        @error('payment_reference')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<div class="mb-3">
    <label for="notes" class="form-label fw-semibold">Clinical Notes / Prescription Instructions</label>
    <textarea id="notes" name="notes" rows="2" class="form-control @error('notes') is-invalid @enderror"
        placeholder="Add any specific clinical notes, dental observations, patient instructions, or dosages...">{{ old('notes', $patientTreatment->notes ?? '') }}</textarea>
    @error('notes')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

{{-- ================= JAVASCRIPT LOGIC ================= --}}
<script>
    // All available data passed from Laravel
    const catalogPatients = @json($patients);
    const catalogTreatments = @json($treatments);
    const patientAppointmentsMap = @json($patientAppointments);

    // Initial state
    let selectedProcedures = @json($initialProcedures);

    // DOM Elements - Patient Search
    const patientSearchInput = document.getElementById('patient_search_input');
    const patientSearchDropdown = document.getElementById('patient_search_dropdown');
    const patientIdInput = document.getElementById('patient_id');
    const appointmentIdInput = document.getElementById('appointment_id');
    const clearPatientBtn = document.getElementById('clear_patient_btn');
    const apptCalloutBanner = document.getElementById('appointment_callout_banner');
    const bannerApptTitle = document.getElementById('banner_appt_title');
    const bannerApptSubtitle = document.getElementById('banner_appt_subtitle');

    // DOM Elements - Procedures
    const proceduresContainer = document.getElementById('procedures-container');
    const btnAddProcedure = document.getElementById('btn-add-procedure');
    const primaryTreatmentIdInput = document.getElementById('treatment_id');
    const primaryProcedureNameInput = document.getElementById('procedure_name');

    // DOM Elements - Pricing
    const priceInput = document.getElementById('price');
    const discountInput = document.getElementById('discount');
    const dtTypeFixed = document.getElementById('dt_type_fixed');
    const dtTypePercentage = document.getElementById('dt_type_percentage');
    const wrapperDiscountFixed = document.getElementById('wrapper_discount_fixed');
    const wrapperDiscountPercentage = document.getElementById('wrapper_discount_percentage');
    const discountPercentageInput = document.getElementById('discount_percentage');
    const dtCalcBadge = document.getElementById('dt_calc_badge');
    const treatmentNetPreview = document.getElementById('treatment-net-preview');

    // DOM Elements - Grand Total & Billing
    const grandTotalPreview = document.getElementById('grand-total-preview');

    const paidInput = document.getElementById('paid_amount');
    const duePreview = document.getElementById('due-preview');
    const statusBadge = document.getElementById('payment-status-badge');
    const dueHint = document.getElementById('due-hint');
    const btnFullPaid = document.getElementById('btn-full-paid');
    const btnFullDue = document.getElementById('btn-full-due');
    const paymentMethodSelect = document.getElementById('payment_method');

    let userCustomizedPaid = (paidInput && paidInput.value && paidInput.value.trim() !== '');

    // ==========================================
    // 1. PATIENT SEARCH DROPDOWN LOGIC
    // ==========================================
    function searchPatients(query) {
        if (!query || query.trim() === '') {
            renderPatientDropdown(catalogPatients.slice(0, 8), false);
            return;
        }

        const q = query.toLowerCase().trim();
        const matches = catalogPatients.filter(p => {
            const name = (p.first_name + ' ' + (p.last_name || '')).toLowerCase();
            const mr = (p.patient_number || '').toLowerCase();
            const phone = (p.phone || '').toLowerCase();
            return name.includes(q) || mr.includes(q) || phone.includes(q);
        });

        renderPatientDropdown(matches, true, query);
    }

    function renderPatientDropdown(patientsList, isFiltered, queryText = '') {
        if (patientsList.length === 0) {
            patientSearchDropdown.innerHTML = `
                <div class="p-3 text-center text-muted">
                    <i class="bi bi-person-x fs-4 d-block mb-1"></i>
                    No patient found matching "<strong>${escapeHtml(queryText)}</strong>".
                    <div class="small mt-1"><a href="{{ route('patients.create') }}" target="_blank" class="text-primary text-decoration-none">+ Register New Patient</a></div>
                </div>
            `;
            patientSearchDropdown.style.display = 'block';
            return;
        }

        let html = '';
        patientsList.forEach(p => {
            const fullName = p.first_name + ' ' + (p.last_name || '');
            const appt = patientAppointmentsMap[p.id];
            let apptBadge = '';
            if (appt) {
                apptBadge = `<span class="badge bg-success-subtle text-success border border-success-subtle ms-2"><i class="bi bi-calendar2-check me-1"></i>Booked: ${escapeHtml(appt.treatment_name)}</span>`;
            }

            const initialLetter = (p.first_name || 'P').charAt(0).toUpperCase();

            html += `
                <a href="javascript:void(0)" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between p-2 patient-dropdown-item" data-id="${p.id}">
                    <div class="d-flex align-items-center overflow-hidden me-2">
                        <div class="rounded-circle bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center me-2 flex-shrink-0" style="width: 36px; height: 36px; font-size: 14px;">
                            ${initialLetter}
                        </div>
                        <div class="text-truncate">
                            <div class="fw-bold text-dark text-truncate">${escapeHtml(fullName)} ${apptBadge}</div>
                            <div class="small text-muted text-truncate">
                                <span class="badge bg-light text-secondary border me-1">${escapeHtml(p.patient_number || 'P-0000')}</span>
                                <i class="bi bi-telephone text-secondary me-1"></i>${escapeHtml(p.phone || 'No phone recorded')}
                            </div>
                        </div>
                    </div>
                    <div class="text-end flex-shrink-0">
                        <span class="btn btn-sm btn-outline-primary py-0 px-2">Select</span>
                    </div>
                </a>
            `;
        });

        patientSearchDropdown.innerHTML = html;
        patientSearchDropdown.style.display = 'block';

        patientSearchDropdown.querySelectorAll('.patient-dropdown-item').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                const pid = parseInt(this.dataset.id, 10);
                selectPatient(pid);
            });
        });
    }

    function selectPatient(patientId) {
        const patient = catalogPatients.find(p => p.id === patientId);
        if (!patient) return;

        patientIdInput.value = patient.id;
        const fullName = patient.first_name + ' ' + (patient.last_name || '');
        patientSearchInput.value = `${patient.patient_number} — ${fullName}` + (patient.phone ? ` (${patient.phone})` : '');
        patientSearchDropdown.style.display = 'none';
        clearPatientBtn.style.display = 'inline-block';

        // Check booked appointment for this patient
        const appt = patientAppointmentsMap[patient.id];
        if (appt) {
            appointmentIdInput.value = appt.appointment_id || '';
            bannerApptTitle.textContent = `📅 Booked Appointment: ${appt.treatment_name || 'General Consultation'}`;
            bannerApptSubtitle.innerHTML = `Scheduled for <span class="fw-semibold text-dark">${appt.appointment_date || ''} (${appt.appointment_time || ''})</span>. Procedure and catalog price automatically pre-selected below.`;
            apptCalloutBanner.style.display = 'flex';

            // Auto-select treatment in procedure rows if booked
            if (appt.treatments && appt.treatments.length > 0) {
                selectedProcedures = appt.treatments.map(t => ({
                    treatment_id: t.treatment_id,
                    name: t.name,
                    price: parseFloat(t.price) || 0,
                    notes: 'Booked appointment on ' + (appt.appointment_date || ''),
                }));
                renderProcedures();
            } else if (appt.treatment_id) {
                const treatment = catalogTreatments.find(t => t.id === appt.treatment_id);
                selectedProcedures = [{
                    treatment_id: appt.treatment_id,
                    name: treatment ? treatment.name : appt.treatment_name,
                    price: treatment ? parseFloat(treatment.price) : (appt.treatment_price || 0),
                    notes: 'Booked appointment on ' + (appt.appointment_date || ''),
                }];
                renderProcedures();
            }
        } else {
            appointmentIdInput.value = '';
            apptCalloutBanner.style.display = 'none';
        }

        recalculateAll(!userCustomizedPaid);
    }

    function clearPatient() {
        patientIdInput.value = '';
        appointmentIdInput.value = '';
        patientSearchInput.value = '';
        clearPatientBtn.style.display = 'none';
        apptCalloutBanner.style.display = 'none';
        patientSearchInput.focus();
    }

    patientSearchInput.addEventListener('input', function() {
        searchPatients(this.value);
    });

    patientSearchInput.addEventListener('focus', function() {
        searchPatients(this.value);
    });

    clearPatientBtn.addEventListener('click', clearPatient);

    document.addEventListener('click', function(e) {
        const wrapper = document.getElementById('patient-search-wrapper');
        if (wrapper && !wrapper.contains(e.target)) {
            patientSearchDropdown.style.display = 'none';
        }
    });

    // ==========================================
    // 2. PROCEDURES PERFORMED BUILDER LOGIC
    // ==========================================
    function renderProcedures() {
        proceduresContainer.innerHTML = '';

        if (selectedProcedures.length === 0) {
            selectedProcedures.push({ treatment_id: null, name: '', price: 0, notes: '' });
        }

        selectedProcedures.forEach((proc, index) => {
            const rowCard = document.createElement('div');
            rowCard.className = 'border rounded-3 p-3 bg-white shadow-sm procedure-row-card position-relative';
            rowCard.dataset.index = index;

            let optionsHtml = '<option value="">-- Choose Dental Treatment from Catalog --</option>';
            let foundInCatalog = false;

            catalogTreatments.forEach(t => {
                const isSelected = proc.treatment_id && (parseInt(proc.treatment_id, 10) === parseInt(t.id, 10));
                if (isSelected) foundInCatalog = true;
                optionsHtml += `<option value="${t.id}" data-price="${t.price}" ${isSelected ? 'selected' : ''}>${escapeHtml(t.name)} &mdash; (PKR ${parseFloat(t.price).toFixed(2)})</option>`;
            });

            const isCustom = !foundInCatalog && (proc.name || proc.treatment_id === 'custom' || (!proc.treatment_id && proc.price > 0));
            optionsHtml += `<option value="custom" ${isCustom ? 'selected' : ''}>➕ [ Other / Custom Walk-in Procedure ]</option>`;

            const deleteBtnHtml = selectedProcedures.length > 1
                ? `<button type="button" class="btn btn-outline-danger btn-sm remove-procedure-btn" data-index="${index}" title="Remove this procedure"><i class="bi bi-trash"></i></button>`
                : '';

            rowCard.innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-primary text-white">Procedure #${index + 1}</span>
                    <div>${deleteBtnHtml}</div>
                </div>

                <div class="row g-2 align-items-start">
                    <div class="col-md-5">
                        <label class="form-label small fw-semibold text-secondary mb-1">Catalog Treatment</label>
                        <select class="form-select form-select-sm proc-catalog-select" data-index="${index}">
                            ${optionsHtml}
                        </select>
                        <input type="hidden" name="procedures[${index}][treatment_id]" id="proc_tid_${index}" value="${proc.treatment_id || ''}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-secondary mb-1">Procedure Fee (PKR)</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white">PKR</span>
                            <input type="number" step="0.01" min="0" class="form-control form-control-sm text-end proc-price-input"
                                name="procedures[${index}][price]" value="${parseFloat(proc.price || 0).toFixed(2)}" data-index="${index}" required>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-secondary mb-1">Tooth # / Note <span class="text-muted fw-normal">(optional)</span></label>
                        <input type="text" class="form-control form-control-sm proc-notes-input"
                            name="procedures[${index}][notes]" value="${escapeHtml(proc.notes || '')}" placeholder="e.g. Tooth #18, Upper arch..." data-index="${index}">
                    </div>
                </div>

                <!-- Custom Procedure Name Input (Shown when custom procedure is selected) -->
                <div class="mt-2 custom-name-wrapper" id="custom_name_wrapper_${index}" style="${isCustom ? '' : 'display:none;'}">
                    <label class="form-label small fw-semibold text-primary mb-1">
                        <i class="bi bi-pencil-square me-1"></i> Custom Procedure Title / Walk-in Description <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control form-control-sm proc-custom-name-input"
                        name="procedures[${index}][name]" id="proc_name_${index}" value="${escapeHtml(proc.name || '')}"
                        placeholder="e.g. Emergency Temporary Filling, Suture Removal, Broken Clasp Repair..." data-index="${index}">
                    <div class="form-text small text-muted">Enter specific walk-in procedure or surgery performed.</div>
                </div>
            `;

            proceduresContainer.appendChild(rowCard);
        });

        // Attach event listeners to procedure rows
        document.querySelectorAll('.proc-catalog-select').forEach(sel => {
            sel.addEventListener('change', function() {
                const idx = parseInt(this.dataset.index, 10);
                const val = this.value;
                const customWrapper = document.getElementById(`custom_name_wrapper_${idx}`);
                const tidInput = document.getElementById(`proc_tid_${idx}`);
                const nameInput = document.getElementById(`proc_name_${idx}`);
                const priceInputElem = document.querySelector(`.proc-price-input[data-index="${idx}"]`);

                if (val === 'custom') {
                    selectedProcedures[idx].treatment_id = null;
                    tidInput.value = '';
                    customWrapper.style.display = 'block';
                    nameInput.focus();
                } else if (val) {
                    const catalogItem = catalogTreatments.find(t => t.id === parseInt(val, 10));
                    selectedProcedures[idx].treatment_id = parseInt(val, 10);
                    selectedProcedures[idx].name = catalogItem ? catalogItem.name : '';
                    selectedProcedures[idx].price = catalogItem ? parseFloat(catalogItem.price) : 0;
                    tidInput.value = val;
                    nameInput.value = catalogItem ? catalogItem.name : '';
                    priceInputElem.value = (catalogItem ? parseFloat(catalogItem.price) : 0).toFixed(2);
                    customWrapper.style.display = 'none';
                } else {
                    selectedProcedures[idx].treatment_id = null;
                    selectedProcedures[idx].name = '';
                    selectedProcedures[idx].price = 0;
                    tidInput.value = '';
                    nameInput.value = '';
                    priceInputElem.value = '0.00';
                    customWrapper.style.display = 'none';
                }

                syncProceduresSum();
                recalculateAll(!userCustomizedPaid);
            });
        });

        document.querySelectorAll('.proc-price-input').forEach(inp => {
            inp.addEventListener('input', function() {
                const idx = parseInt(this.dataset.index, 10);
                selectedProcedures[idx].price = Math.max(0, parseFloat(this.value) || 0);
                syncProceduresSum();
                recalculateAll(!userCustomizedPaid);
            });
        });

        document.querySelectorAll('.proc-custom-name-input').forEach(inp => {
            inp.addEventListener('input', function() {
                const idx = parseInt(this.dataset.index, 10);
                selectedProcedures[idx].name = this.value;
                syncProceduresSum();
            });
        });

        document.querySelectorAll('.proc-notes-input').forEach(inp => {
            inp.addEventListener('input', function() {
                const idx = parseInt(this.dataset.index, 10);
                selectedProcedures[idx].notes = this.value;
            });
        });

        document.querySelectorAll('.remove-procedure-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const idx = parseInt(this.dataset.index, 10);
                if (selectedProcedures.length > 1) {
                    selectedProcedures.splice(idx, 1);
                    renderProcedures();
                    syncProceduresSum();
                    recalculateAll(!userCustomizedPaid);
                }
            });
        });
    }

    function syncProceduresSum() {
        let total = 0;
        const names = [];
        let firstCatalogId = null;

        selectedProcedures.forEach(p => {
            total += (parseFloat(p.price) || 0);
            if (p.name && p.name.trim() !== '') {
                names.push(p.name.trim());
            }
            if (!firstCatalogId && p.treatment_id) {
                firstCatalogId = p.treatment_id;
            }
        });

        priceInput.value = total.toFixed(2);
        primaryTreatmentIdInput.value = firstCatalogId || '';
        primaryProcedureNameInput.value = names.join(' + ');
    }

    btnAddProcedure.addEventListener('click', function() {
        selectedProcedures.push({
            treatment_id: null,
            name: '',
            price: 0,
            notes: '',
        });
        renderProcedures();
    });

    // ==========================================
    // 3. DISCOUNT & TOTALS RECALCULATION
    // ==========================================
    function escapeHtml(string) {
        const div = document.createElement('div');
        div.innerText = string || '';
        return div.innerHTML;
    }

    function syncTreatmentDiscountMode() {
        if (dtTypePercentage && dtTypePercentage.checked) {
            wrapperDiscountFixed.style.display = 'none';
            wrapperDiscountPercentage.style.display = 'block';
        } else {
            wrapperDiscountFixed.style.display = 'block';
            wrapperDiscountPercentage.style.display = 'none';
        }
    }

    if (dtTypeFixed && dtTypePercentage) {
        dtTypeFixed.addEventListener('change', function() {
            if (this.checked) {
                const tPrice = Math.max(0, parseFloat(priceInput.value) || 0);
                const pct = Math.max(0, parseFloat(discountPercentageInput.value) || 0);
                if (pct > 0 && tPrice > 0) {
                    discountInput.value = Math.round(tPrice * (pct / 100) * 100 / 100).toFixed(2);
                }
                syncTreatmentDiscountMode();
                recalculateAll(!userCustomizedPaid);
            }
        });
        dtTypePercentage.addEventListener('change', function() {
            if (this.checked) {
                const tPrice = Math.max(0, parseFloat(priceInput.value) || 0);
                const flat = Math.max(0, parseFloat(discountInput.value) || 0);
                if (flat > 0 && tPrice > 0 && (!discountPercentageInput.value || parseFloat(discountPercentageInput.value) === 0)) {
                    discountPercentageInput.value = Math.min(100, Math.round((flat / tPrice * 100) * 10) / 10);
                }
                syncTreatmentDiscountMode();
                recalculateAll(!userCustomizedPaid);
            }
        });
    }

    function recalculateAll(syncPaid = false) {
        // 1. Treatment procedure pricing
        const tPrice = Math.max(0, parseFloat(priceInput.value) || 0);
        let tDisc = 0;

        if (dtTypePercentage && dtTypePercentage.checked) {
            const pct = Math.min(100, Math.max(0, parseFloat(discountPercentageInput.value) || 0));
            tDisc = Math.round(tPrice * (pct / 100) * 100) / 100;
            if (dtCalcBadge) dtCalcBadge.textContent = '= PKR ' + tDisc.toFixed(2) + ' off';
            if (discountInput) discountInput.value = tDisc.toFixed(2);
        } else {
            tDisc = Math.max(0, parseFloat(discountInput.value) || 0);
        }

        const tNet = Math.max(0, tPrice - tDisc);
        treatmentNetPreview.textContent = 'PKR ' + tNet.toFixed(2);

        // 2. Grand Total is Procedure Net Total
        const grandTotal = tNet;
        grandTotalPreview.textContent = 'PKR ' + grandTotal.toFixed(2);

        // 3. Payment & Due calculations
        if (paidInput) {
            if (syncPaid || (!userCustomizedPaid && (!paidInput.value || paidInput.value === ''))) {
                paidInput.value = grandTotal > 0 ? grandTotal.toFixed(2) : '0.00';
            }

            const paid = parseFloat(paidInput.value) || 0;
            const due = Math.max(0, grandTotal - paid);
            duePreview.textContent = 'PKR ' + due.toFixed(2);

            if (due === 0 && grandTotal > 0) {
                statusBadge.className = 'badge bg-success';
                statusBadge.textContent = 'Paid';
                dueHint.textContent = 'Invoice will be fully settled.';
                if (paymentMethodSelect) paymentMethodSelect.disabled = false;
            } else if (paid > 0 && due > 0) {
                statusBadge.className = 'badge bg-warning text-dark';
                statusBadge.textContent = 'Partial Due';
                dueHint.textContent = 'Partial payment of PKR ' + paid.toFixed(2) + ' recorded. Due: PKR ' + due.toFixed(2);
                if (paymentMethodSelect) paymentMethodSelect.disabled = false;
            } else if (grandTotal > 0 && paid === 0) {
                statusBadge.className = 'badge bg-danger';
                statusBadge.textContent = 'Unpaid';
                dueHint.textContent = 'Full amount due. Invoice marked as pending.';
                if (paymentMethodSelect) paymentMethodSelect.disabled = true;
            } else {
                statusBadge.className = 'badge bg-secondary';
                statusBadge.textContent = 'Zero Total';
                dueHint.textContent = 'No charges recorded.';
                if (paymentMethodSelect) paymentMethodSelect.disabled = false;
            }
        }
    }

    priceInput.addEventListener('input', function() { recalculateAll(!userCustomizedPaid); });
    discountInput.addEventListener('input', function() { recalculateAll(!userCustomizedPaid); });
    if (discountPercentageInput) {
        discountPercentageInput.addEventListener('input', function() { recalculateAll(!userCustomizedPaid); });
    }

    syncTreatmentDiscountMode();

    if (paidInput) {
        paidInput.addEventListener('input', function() {
            userCustomizedPaid = true;
            recalculateAll(false);
        });

        if (btnFullPaid) {
            btnFullPaid.addEventListener('click', function() {
                userCustomizedPaid = false;
                recalculateAll(true);
            });
        }

        if (btnFullDue) {
            btnFullDue.addEventListener('click', function() {
                userCustomizedPaid = true;
                paidInput.value = '0.00';
                recalculateAll(false);
            });
        }
    }

    // Initial renders
    renderProcedures();
    syncProceduresSum();
    recalculateAll(false);
</script>
