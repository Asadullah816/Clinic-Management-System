{{-- Expects: $appointment (null on create), $patients, $treatments, $patientId (optional preselect) --}}

@php
    $selectedPatientId = old('patient_id', $appointment->patient_id ?? $patientId);
    $selectedPatient = $selectedPatientId ? collect($patients)->firstWhere('id', (int) $selectedPatientId) : null;
    $selectedPatientText = $selectedPatient
        ? ($selectedPatient->patient_number . ' — ' . $selectedPatient->full_name . ($selectedPatient->phone ? ' (' . $selectedPatient->phone . ')' : ''))
        : '';

    // Selected treatments (first is required, others optional)
    $selectedTreatmentIds = [];
    if (old('treatment_ids')) {
        $selectedTreatmentIds = array_values(array_filter(old('treatment_ids')));
    } elseif (isset($appointment) && $appointment) {
        $selectedTreatmentIds = $appointment->treatments->isNotEmpty()
            ? $appointment->treatments->pluck('id')->toArray()
            : ($appointment->treatment_id ? [$appointment->treatment_id] : []);
    }
    if (empty($selectedTreatmentIds)) {
        $selectedTreatmentIds = [''];
    }

    $minDate = today()->toDateString();
    $currentDateVal = old('appointment_date', $appointment?->appointment_date?->format('Y-m-d') ?? $minDate);
@endphp

<div class="row mb-3">
    <!-- Searchable Patient Selection -->
    <div class="col-md-6 mb-3 mb-md-0 position-relative" id="appt-patient-search-wrapper">
        <label for="appt_patient_search_input" class="form-label fw-semibold">
            Patient <span class="text-danger">*</span>
            <span class="text-muted fw-normal small ms-1">(Instant search by Name, MR #, or Phone)</span>
        </label>
        <div class="input-group">
            <span class="input-group-text bg-white text-primary"><i class="bi bi-person-search"></i></span>
            <input type="text" id="appt_patient_search_input" class="form-control @error('patient_id') is-invalid @enderror"
                placeholder="Search patient by name, MR # (P-0001), or phone..."
                autocomplete="off" value="{{ $selectedPatientText }}">
            <button class="btn btn-outline-secondary" type="button" id="clear_appt_patient_btn"
                style="{{ $selectedPatient ? '' : 'display:none;' }}" title="Change patient">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <input type="hidden" name="patient_id" id="patient_id" value="{{ $selectedPatientId }}" required>

        @error('patient_id')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror

        <!-- Patient Search Results Dropdown -->
        <div id="appt_patient_dropdown" class="list-group position-absolute w-100 shadow-lg rounded-3 border mt-1"
            style="max-height: 280px; overflow-y: auto; z-index: 1060; display: none; background: #ffffff;">
        </div>
    </div>

    <!-- Appointment Status -->
    <div class="col-md-6">
        <label for="status" class="form-label fw-semibold">Appointment Status <span class="text-danger">*</span></label>
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

{{-- ================= MULTI-TREATMENT SELECTION (First is required, others optional) ================= --}}
<div class="card mb-3 border shadow-sm">
    <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
        <span class="fw-semibold text-dark">
            <i class="bi bi-bandaid text-primary me-1"></i> Treatments / Procedures to Perform
        </span>
        <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" id="btn-add-treatment-slot">
            <i class="bi bi-plus-lg me-1"></i> Add Another Treatment
        </button>
    </div>
    <div class="card-body">
        <div class="text-muted small mb-2">
            The <strong>primary treatment</strong> is required. You can add additional optional treatments for multi-procedure visits.
        </div>

        @error('treatment_ids.0')
            <div class="alert alert-danger py-1 px-2 mb-2 small">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $message }}
            </div>
        @enderror

        <div id="treatments-wrapper" class="d-flex flex-column gap-2">
            <!-- Row 1: Primary Required Treatment -->
            <div class="treatment-select-row p-2 border rounded bg-light-subtle">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="badge bg-primary">1. Primary Treatment (Required)</span>
                </div>
                <select name="treatment_ids[]" class="form-select appt-treatment-select @error('treatment_ids.0') is-invalid @enderror" required>
                    <option value="">-- Select primary treatment --</option>
                    @foreach ($treatments as $treatment)
                        <option value="{{ $treatment->id }}" data-price="{{ $treatment->price }}" data-duration="{{ $treatment->duration }}"
                            {{ (string) ($selectedTreatmentIds[0] ?? '') === (string) $treatment->id ? 'selected' : '' }}>
                            {{ $treatment->name }} &mdash; PKR {{ number_format($treatment->price, 2) }}
                            @if ($treatment->duration) ({{ $treatment->duration }} min) @endif
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Additional Optional Treatments -->
            @for ($i = 1; $i < count($selectedTreatmentIds); $i++)
                <div class="treatment-select-row p-2 border rounded bg-white position-relative">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="badge bg-secondary">Additional Treatment #{{ $i + 1 }} (Optional)</span>
                        <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1 btn-remove-treatment" title="Remove">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                    <select name="treatment_ids[]" class="form-select form-select-sm appt-treatment-select">
                        <option value="">-- Select additional treatment (Optional) --</option>
                        @foreach ($treatments as $treatment)
                            <option value="{{ $treatment->id }}" data-price="{{ $treatment->price }}" data-duration="{{ $treatment->duration }}"
                                {{ (string) $selectedTreatmentIds[$i] === (string) $treatment->id ? 'selected' : '' }}>
                                {{ $treatment->name }} &mdash; PKR {{ number_format($treatment->price, 2) }}
                                @if ($treatment->duration) ({{ $treatment->duration }} min) @endif
                            </option>
                        @endforeach
                    </select>
                </div>
            @endfor
        </div>

        <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top text-muted small">
            <div>Estimated Total: <span class="fw-bold text-primary" id="appt-treatments-total">PKR 0.00</span></div>
            <div id="appt-treatments-count">1 treatment selected</div>
        </div>
    </div>
</div>

{{-- ================= DATE, TIME & CONFLICT PREVENTION ================= --}}
<div class="card mb-3 border shadow-sm">
    <div class="card-header bg-light py-2">
        <span class="fw-semibold text-dark">
            <i class="bi bi-clock-history text-primary me-1"></i> Date &amp; Time Slot Scheduling
        </span>
    </div>
    <div class="card-body">
        <div class="row">
            <!-- Date input with min=today to prevent past dates -->
            <div class="col-md-6 mb-3">
                <label for="appointment_date" class="form-label fw-semibold">
                    Appointment Date <span class="text-danger">*</span>
                </label>
                <input type="date" id="appointment_date" name="appointment_date"
                    min="{{ $minDate }}"
                    value="{{ $currentDateVal }}"
                    class="form-control @error('appointment_date') is-invalid @enderror" required>
                <div class="form-text small">
                    <i class="bi bi-shield-check text-success me-1"></i> Past dates are strictly prohibited.
                </div>
                @error('appointment_date')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <!-- Time input -->
            <div class="col-md-6 mb-3">
                <label for="appointment_time" class="form-label fw-semibold">
                    Time Slot <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-clock"></i></span>
                    <input type="time" id="appointment_time" name="appointment_time"
                        value="{{ old('appointment_time', $appointment ? substr($appointment->appointment_time, 0, 5) : '') }}"
                        class="form-control @error('appointment_time') is-invalid @enderror" required>
                </div>
                <div class="form-text small">Standard 24-hr format (e.g. 15:00 for 03:00 PM).</div>
                @error('appointment_time')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- Real-Time Slot Conflict & Availability Banner -->
        <div id="slot-conflict-alert" class="alert py-2 px-3 mb-2 small d-flex align-items-center justify-content-between" style="display: none;">
            <div class="d-flex align-items-center">
                <i class="bi fs-5 me-2" id="slot-alert-icon"></i>
                <span id="slot-alert-text"></span>
            </div>
        </div>

        <!-- Booked Slots on Selected Date -->
        <div class="p-2 rounded bg-light border">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="small fw-semibold text-secondary">
                    <i class="bi bi-calendar-event me-1"></i> Already Booked Slots on <span id="current-day-label">Selected Date</span>:
                </span>
                <span class="badge bg-secondary-subtle text-secondary" id="booked-count-badge">0 booked</span>
            </div>
            <div id="booked-slots-badges" class="d-flex flex-wrap gap-1">
                <span class="text-muted small fst-italic">No booked appointments on this day yet. All slots are open.</span>
            </div>
        </div>

        <!-- Quick Slot Buttons -->
        <div class="mt-2">
            <div class="small text-muted mb-1">Quick Select Common Clinic Slots:</div>
            <div class="d-flex flex-wrap gap-1">
                @php
                    $commonSlots = ['10:00', '11:00', '12:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00', '20:00'];
                @endphp
                @foreach ($commonSlots as $slot)
                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 quick-slot-btn" data-time="{{ $slot }}">
                        {{ \Carbon\Carbon::createFromFormat('H:i', $slot)->format('h:i A') }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="mb-3">
    <label for="notes" class="form-label fw-semibold">Notes / Patient Instructions</label>
    <textarea id="notes" name="notes" rows="2" class="form-control @error('notes') is-invalid @enderror"
        placeholder="e.g. Patient requested Dr. Badshah Afsar, follow-up after RCT, tooth sensitivity...">{{ old('notes', $appointment->notes ?? '') }}</textarea>
    @error('notes')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

{{-- ================= JAVASCRIPT ================= --}}
<script>
    const catalogPatients = @json($patients);
    const catalogTreatments = @json($treatments);
    const appointmentId = {{ isset($appointment) ? $appointment->id : 'null' }};
    const bookedSlotsUrl = "{{ route('appointments.booked-slots') }}";

    // DOM Elements - Patient
    const patientSearchInput = document.getElementById('appt_patient_search_input');
    const patientDropdown = document.getElementById('appt_patient_dropdown');
    const patientIdInput = document.getElementById('patient_id');
    const clearPatientBtn = document.getElementById('clear_appt_patient_btn');

    // DOM Elements - Date & Time
    const dateInput = document.getElementById('appointment_date');
    const timeInput = document.getElementById('appointment_time');
    const slotConflictAlert = document.getElementById('slot-conflict-alert');
    const slotAlertIcon = document.getElementById('slot-alert-icon');
    const slotAlertText = document.getElementById('slot-alert-text');
    const currentDayLabel = document.getElementById('current-day-label');
    const bookedSlotsBadges = document.getElementById('booked-slots-badges');
    const bookedCountBadge = document.getElementById('booked-count-badge');

    // DOM Elements - Treatments
    const treatmentsWrapper = document.getElementById('treatments-wrapper');
    const btnAddTreatmentSlot = document.getElementById('btn-add-treatment-slot');
    const apptTreatmentsTotal = document.getElementById('appt-treatments-total');
    const apptTreatmentsCount = document.getElementById('appt-treatments-count');

    let currentDayBookedSlots = [];

    // ==========================================
    // 1. PATIENT SEARCH LOGIC
    // ==========================================
    function searchApptPatients(query) {
        if (!query || query.trim() === '') {
            renderApptPatientDropdown(catalogPatients.slice(0, 8), false);
            return;
        }

        const q = query.toLowerCase().trim();
        const matches = catalogPatients.filter(p => {
            const name = (p.first_name + ' ' + (p.last_name || '')).toLowerCase();
            const mr = (p.patient_number || '').toLowerCase();
            const phone = (p.phone || '').toLowerCase();
            return name.includes(q) || mr.includes(q) || phone.includes(q);
        });

        renderApptPatientDropdown(matches, true, query);
    }

    function renderApptPatientDropdown(patientsList, isFiltered, queryText = '') {
        if (patientsList.length === 0) {
            patientDropdown.innerHTML = `
                <div class="p-3 text-center text-muted">
                    <i class="bi bi-person-x fs-4 d-block mb-1"></i>
                    No patient found matching "<strong>${escapeHtml(queryText)}</strong>".
                    <div class="small mt-1"><a href="{{ route('patients.create') }}" target="_blank" class="text-primary text-decoration-none">+ Register New Patient</a></div>
                </div>
            `;
            patientDropdown.style.display = 'block';
            return;
        }

        let html = '';
        patientsList.forEach(p => {
            const fullName = p.first_name + ' ' + (p.last_name || '');
            const initialLetter = (p.first_name || 'P').charAt(0).toUpperCase();

            html += `
                <a href="javascript:void(0)" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between p-2 appt-patient-dropdown-item" data-id="${p.id}">
                    <div class="d-flex align-items-center overflow-hidden me-2">
                        <div class="rounded-circle bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center me-2 flex-shrink-0" style="width: 34px; height: 34px; font-size: 13px;">
                            ${initialLetter}
                        </div>
                        <div class="text-truncate">
                            <div class="fw-bold text-dark text-truncate">${escapeHtml(fullName)}</div>
                            <div class="small text-muted text-truncate">
                                <span class="badge bg-light text-secondary border me-1">${escapeHtml(p.patient_number || 'P-0000')}</span>
                                <i class="bi bi-telephone text-secondary me-1"></i>${escapeHtml(p.phone || 'No phone')}
                            </div>
                        </div>
                    </div>
                    <div class="text-end flex-shrink-0">
                        <span class="btn btn-sm btn-outline-primary py-0 px-2">Select</span>
                    </div>
                </a>
            `;
        });

        patientDropdown.innerHTML = html;
        patientDropdown.style.display = 'block';

        patientDropdown.querySelectorAll('.appt-patient-dropdown-item').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                const pid = parseInt(this.dataset.id, 10);
                selectApptPatient(pid);
            });
        });
    }

    function selectApptPatient(patientId) {
        const patient = catalogPatients.find(p => p.id === patientId);
        if (!patient) return;

        patientIdInput.value = patient.id;
        const fullName = patient.first_name + ' ' + (patient.last_name || '');
        patientSearchInput.value = `${patient.patient_number} — ${fullName}` + (patient.phone ? ` (${patient.phone})` : '');
        patientDropdown.style.display = 'none';
        clearPatientBtn.style.display = 'inline-block';
    }

    function clearApptPatient() {
        patientIdInput.value = '';
        patientSearchInput.value = '';
        clearPatientBtn.style.display = 'none';
        patientSearchInput.focus();
    }

    patientSearchInput.addEventListener('input', function() { searchApptPatients(this.value); });
    patientSearchInput.addEventListener('focus', function() { searchApptPatients(this.value); });
    clearPatientBtn.addEventListener('click', clearApptPatient);

    document.addEventListener('click', function(e) {
        const wrapper = document.getElementById('appt-patient-search-wrapper');
        if (wrapper && !wrapper.contains(e.target)) {
            patientDropdown.style.display = 'none';
        }
    });

    // ==========================================
    // 2. MULTI-TREATMENTS BUILDER
    // ==========================================
    function recalculateTreatmentsSummary() {
        let total = 0;
        let count = 0;

        document.querySelectorAll('.appt-treatment-select').forEach(sel => {
            const opt = sel.options[sel.selectedIndex];
            if (opt && opt.value && opt.dataset.price) {
                total += parseFloat(opt.dataset.price);
                count++;
            }
        });

        apptTreatmentsTotal.textContent = 'PKR ' + total.toFixed(2);
        apptTreatmentsCount.textContent = count + ' ' + (count === 1 ? 'treatment' : 'treatments') + ' selected';
    }

    function attachTreatmentRowListeners(row) {
        const select = row.querySelector('.appt-treatment-select');
        if (select) {
            select.addEventListener('change', recalculateTreatmentsSummary);
        }

        const removeBtn = row.querySelector('.btn-remove-treatment');
        if (removeBtn) {
            removeBtn.addEventListener('click', function() {
                row.remove();
                recalculateTreatmentsSummary();
            });
        }
    }

    document.querySelectorAll('.treatment-select-row').forEach(attachTreatmentRowListeners);

    btnAddTreatmentSlot.addEventListener('click', function() {
        const existingRows = treatmentsWrapper.querySelectorAll('.treatment-select-row').length;
        const rowDiv = document.createElement('div');
        rowDiv.className = 'treatment-select-row p-2 border rounded bg-white position-relative';

        let optionsHtml = '<option value="">-- Select additional treatment (Optional) --</option>';
        catalogTreatments.forEach(t => {
            optionsHtml += `<option value="${t.id}" data-price="${t.price}" data-duration="${t.duration || ''}">${escapeHtml(t.name)} &mdash; PKR ${parseFloat(t.price).toFixed(2)}${t.duration ? ' (' + t.duration + ' min)' : ''}</option>`;
        });

        rowDiv.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="badge bg-secondary">Additional Treatment #${existingRows + 1} (Optional)</span>
                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1 btn-remove-treatment" title="Remove">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
            <select name="treatment_ids[]" class="form-select form-select-sm appt-treatment-select">
                ${optionsHtml}
            </select>
        `;

        treatmentsWrapper.appendChild(rowDiv);
        attachTreatmentRowListeners(rowDiv);
        recalculateTreatmentsSummary();
    });

    // ==========================================
    // 3. DATE & TIME CONFLICT CHECKING
    // ==========================================
    function checkSlotAvailability() {
        const selectedDate = dateInput.value;
        const selectedTime = timeInput.value ? timeInput.value.substring(0, 5) : '';

        if (!selectedDate) {
            slotConflictAlert.style.display = 'none';
            return;
        }

        currentDayLabel.textContent = selectedDate;

        // Fetch booked slots for the selected date
        let url = `${bookedSlotsUrl}?date=${encodeURIComponent(selectedDate)}`;
        if (appointmentId) {
            url += `&exclude_id=${encodeURIComponent(appointmentId)}`;
        }

        fetch(url)
            .then(res => res.json())
            .then(slots => {
                currentDayBookedSlots = slots;
                renderBookedSlotsBadges(slots);
                validateCurrentTimeSlot(selectedTime, slots);
            })
            .catch(err => {
                console.error('Error fetching booked slots:', err);
            });
    }

    function renderBookedSlotsBadges(slots) {
        bookedCountBadge.textContent = slots.length + ' booked';

        if (slots.length === 0) {
            bookedSlotsBadges.innerHTML = '<span class="text-success small fw-semibold"><i class="bi bi-check-circle me-1"></i>All time slots are completely open on this day.</span>';
            return;
        }

        let html = '';
        slots.forEach(s => {
            html += `
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1" title="Booked for ${escapeHtml(s.patient_name)}">
                    <i class="bi bi-lock-fill me-1"></i>${escapeHtml(s.formatted_time)} (${escapeHtml(s.patient_name)})
                </span>
            `;
        });
        bookedSlotsBadges.innerHTML = html;
    }

    function validateCurrentTimeSlot(timeStr, slots) {
        if (!timeStr) {
            slotConflictAlert.style.display = 'none';
            return;
        }

        // Check if selected time is in the past for today
        const todayStr = (new Date()).toISOString().split('T')[0];
        if (dateInput.value === todayStr) {
            const now = new Date();
            const currentHours = String(now.getHours()).padStart(2, '0');
            const currentMinutes = String(now.getMinutes()).padStart(2, '0');
            const currentTimeStr = `${currentHours}:${currentMinutes}`;

            if (timeStr < currentTimeStr) {
                slotConflictAlert.className = 'alert alert-danger py-2 px-3 mb-2 small d-flex align-items-center justify-content-between';
                slotAlertIcon.className = 'bi bi-x-circle-fill text-danger fs-5 me-2';
                slotAlertText.innerHTML = `<strong>Past Time:</strong> ${timeStr} has already passed today. Please pick a future time slot.`;
                slotConflictAlert.style.display = 'flex';
                return;
            }
        }

        // Check if conflict with an existing appointment
        const conflict = slots.find(s => s.time === timeStr);
        if (conflict) {
            slotConflictAlert.className = 'alert alert-danger py-2 px-3 mb-2 small d-flex align-items-center justify-content-between';
            slotAlertIcon.className = 'bi bi-exclamation-octagon-fill text-danger fs-5 me-2';
            slotAlertText.innerHTML = `<strong>Slot Taken:</strong> ${conflict.formatted_time} is already booked for <strong>${escapeHtml(conflict.patient_name)}</strong> (#${escapeHtml(conflict.patient_number)}). Double booking prohibited.`;
            slotConflictAlert.style.display = 'flex';
        } else {
            slotConflictAlert.className = 'alert alert-success py-2 px-3 mb-2 small d-flex align-items-center justify-content-between';
            slotAlertIcon.className = 'bi bi-check-circle-fill text-success fs-5 me-2';
            slotAlertText.innerHTML = `<strong>Slot Available:</strong> ${timeStr} is open and ready to book.`;
            slotConflictAlert.style.display = 'flex';
        }
    }

    dateInput.addEventListener('change', checkSlotAvailability);
    timeInput.addEventListener('input', function() {
        validateCurrentTimeSlot(this.value ? this.value.substring(0, 5) : '', currentDayBookedSlots);
    });

    document.querySelectorAll('.quick-slot-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            timeInput.value = this.dataset.time;
            validateCurrentTimeSlot(this.dataset.time, currentDayBookedSlots);
        });
    });

    function escapeHtml(string) {
        const div = document.createElement('div');
        div.innerText = string || '';
        return div.innerHTML;
    }

    // Initial check
    checkSlotAvailability();
    recalculateTreatmentsSummary();
</script>
