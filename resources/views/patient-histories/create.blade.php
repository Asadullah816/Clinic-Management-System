@extends('layouts.app')

@section('title', 'Add Historical Patient Record')

@section('content')

    <div class="row justify-content-center">
        <div class="col-lg-10">

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="bi bi-clock-history text-primary me-2"></i>Record Historical Patient (Pre-App Visit)
                        </h5>
                        <div class="text-muted small">
                            Enter records for patients who attended treatments prior to the launch of this clinic application.
                        </div>
                    </div>
                    <a href="{{ route('patient-histories.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> Back to Directory
                    </a>
                </div>

                <div class="card-body p-4">
                    <form method="POST" action="{{ route('patient-histories.store') }}" id="historicalPatientForm">
                        @csrf

                        {{-- Patient Selection Mode --}}
                        <div class="mb-4 p-3 rounded-3 bg-light border">
                            <label class="form-label fw-bold text-dark mb-2">Patient Selection Type</label>
                            <div class="d-flex gap-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="patient_type" id="type_existing"
                                        value="existing" {{ old('patient_type', $patient ? 'existing' : 'existing') === 'existing' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="type_existing">
                                        <i class="bi bi-person-check me-1 text-primary"></i> Link to Existing Patient in System
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="patient_type" id="type_new"
                                        value="new" {{ old('patient_type') === 'new' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="type_new">
                                        <i class="bi bi-person-plus me-1 text-success"></i> Register New Historical Patient
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- ================= OPTION A: EXISTING PATIENT ================= --}}
                        <div id="existing_patient_section" style="{{ old('patient_type', $patient ? 'existing' : 'existing') === 'existing' ? '' : 'display: none;' }}">
                            <div class="mb-4 position-relative" id="history-patient-search-wrapper">
                                <label for="history_patient_search_input" class="form-label fw-semibold">
                                    Select Patient <span class="text-danger">*</span>
                                    <span class="text-muted fw-normal small ms-1">(Instant search by Name, MR #, or Phone)</span>
                                </label>
                                @php
                                    $selectedPatientId = old('patient_id', $patient?->id);
                                    $selectedPatientObj = $selectedPatientId ? collect($patients)->firstWhere('id', (int) $selectedPatientId) : null;
                                    $selectedPatientText = $selectedPatientObj
                                        ? ($selectedPatientObj->patient_number . ' — ' . $selectedPatientObj->full_name . ($selectedPatientObj->phone ? ' (' . $selectedPatientObj->phone . ')' : ''))
                                        : '';
                                @endphp
                                <div class="input-group">
                                    <span class="input-group-text bg-white text-primary"><i class="bi bi-person-search"></i></span>
                                    <input type="text" id="history_patient_search_input" class="form-control @error('patient_id') is-invalid @enderror"
                                        placeholder="Type patient name, MR # (e.g. P-0001), or phone..."
                                        autocomplete="off" value="{{ $selectedPatientText }}">
                                    <button class="btn btn-outline-secondary" type="button" id="clear_history_patient_btn"
                                        style="{{ $selectedPatientObj ? '' : 'display:none;' }}" title="Change patient">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                                <input type="hidden" name="patient_id" id="patient_id" value="{{ $selectedPatientId }}">

                                @error('patient_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror

                                <div id="history_patient_dropdown" class="list-group position-absolute w-100 shadow-lg rounded-3 border mt-1"
                                    style="max-height: 280px; overflow-y: auto; z-index: 1060; display: none; background: #ffffff;">
                                </div>
                            </div>
                        </div>

                        {{-- ================= OPTION B: REGISTER NEW HISTORICAL PATIENT ================= --}}
                        <div id="new_patient_section" class="card mb-4 border bg-light-subtle" style="{{ old('patient_type') === 'new' ? '' : 'display: none;' }}">
                            <div class="card-header bg-white py-2">
                                <span class="fw-semibold text-primary"><i class="bi bi-person-lines-fill me-1"></i> Patient Demographics</span>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="first_name" class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                                        <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}"
                                            class="form-control @error('first_name') is-invalid @enderror" placeholder="e.g. Tariq">
                                        @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="last_name" class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                                        <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}"
                                            class="form-control @error('last_name') is-invalid @enderror" placeholder="e.g. Mehmood">
                                        @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="phone" class="form-label fw-semibold">Phone <span class="text-danger">*</span></label>
                                        <input type="text" id="phone" name="phone" value="{{ old('phone') }}"
                                            class="form-control @error('phone') is-invalid @enderror" placeholder="03001234567">
                                        @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="gender" class="form-label fw-semibold">Gender <span class="text-danger">*</span></label>
                                        <select name="gender" id="gender" class="form-select @error('gender') is-invalid @enderror">
                                            <option value="">-- Select --</option>
                                            <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Male</option>
                                            <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                                            <option value="other" {{ old('gender') === 'other' ? 'selected' : '' }}>Other</option>
                                        </select>
                                        @error('gender') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="date_of_birth" class="form-label fw-semibold">Date of Birth</label>
                                        <input type="date" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}"
                                            class="form-control @error('date_of_birth') is-invalid @enderror">
                                        @error('date_of_birth') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="address" class="form-label">Address</label>
                                        <input type="text" id="address" name="address" value="{{ old('address') }}"
                                            class="form-control @error('address') is-invalid @enderror" placeholder="City, Area, Address...">
                                        @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="emergency_contact" class="form-label">Emergency Contact Phone</label>
                                        <input type="text" id="emergency_contact" name="emergency_contact" value="{{ old('emergency_contact') }}"
                                            class="form-control @error('emergency_contact') is-invalid @enderror" placeholder="Family member or contact phone">
                                        @error('emergency_contact') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ================= HISTORICAL ATTENDANCE & PROCEDURES ================= --}}
                        <div class="card mb-4 border shadow-sm">
                            <div class="card-header bg-light py-2">
                                <span class="fw-semibold text-dark">
                                    <i class="bi bi-bandaid text-primary me-1"></i> Pre-Application Treatment &amp; Attendance Details
                                </span>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="visit_date" class="form-label fw-semibold">Past Visit / Attendance Date <span class="text-danger">*</span></label>
                                        <input type="date" id="visit_date" name="visit_date"
                                            value="{{ old('visit_date', now()->subMonths(3)->toDateString()) }}"
                                            max="{{ today()->toDateString() }}"
                                            class="form-control @error('visit_date') is-invalid @enderror" required>
                                        <div class="form-text small">Approximate or exact date when patient attended the clinic.</div>
                                        @error('visit_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="doctor_id" class="form-label fw-semibold">Attending Doctor / Dentist</label>
                                        @if (auth()->user()->isDoctor())
                                            <input type="hidden" name="doctor_id" value="{{ auth()->id() }}">
                                            <input type="text" class="form-control bg-light fw-semibold" value="{{ auth()->user()->name }} (You)" readonly>
                                        @else
                                            <select name="doctor_id" id="doctor_id" class="form-select @error('doctor_id') is-invalid @enderror">
                                                <option value="">-- Select from Registered Doctors --</option>
                                                @foreach ($doctors as $doc)
                                                    <option value="{{ $doc->id }}" {{ (string) old('doctor_id') === (string) $doc->id ? 'selected' : '' }}>
                                                        {{ $doc->name }} ({{ $doc->role === 'admin' ? 'Super Admin / Lead' : 'Doctor' }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="mt-2">
                                                <input type="text" name="doctor_name" value="{{ old('doctor_name') }}"
                                                    class="form-control form-control-sm" placeholder="Or type other/previous doctor name if not in list...">
                                            </div>
                                        @endif
                                        @error('doctor_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="procedures" class="form-label fw-semibold">
                                        Procedures / Treatments Performed <span class="text-danger">*</span>
                                    </label>
                                    <textarea id="procedures" name="procedures" rows="3"
                                        class="form-control @error('procedures') is-invalid @enderror" required
                                        placeholder="e.g. Root Canal Treatment on lower right molar #46, PFM Crown placement, Scaling and deep root planing...">{{ old('procedures') }}</textarea>
                                    @error('procedures') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="tooth_numbers" class="form-label fw-semibold">Tooth Numbers / Region (Optional)</label>
                                        <input type="text" id="tooth_numbers" name="tooth_numbers" value="{{ old('tooth_numbers') }}"
                                            class="form-control @error('tooth_numbers') is-invalid @enderror" placeholder="e.g. 11, 21, 46, Upper Arch">
                                        @error('tooth_numbers') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-8 mb-3">
                                        <label for="diagnosis" class="form-label fw-semibold">Previous Diagnosis / Findings (Optional)</label>
                                        <input type="text" id="diagnosis" name="diagnosis" value="{{ old('diagnosis') }}"
                                            class="form-control @error('diagnosis') is-invalid @enderror" placeholder="e.g. Irreversible pulpitis, chronic periapical abscess, severe tartar">
                                        @error('diagnosis') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <div class="mb-0">
                                    <label for="notes" class="form-label fw-semibold">Clinical &amp; General History Notes</label>
                                    <textarea id="notes" name="notes" rows="2"
                                        class="form-control @error('notes') is-invalid @enderror"
                                        placeholder="Any previous case history, allergies noted in paper files, or special instructions...">{{ old('notes') }}</textarea>
                                    @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        {{-- ================= HISTORICAL FINANCIALS & OUTSTANDINGS ================= --}}
                        <div class="card mb-4 border shadow-sm">
                            <div class="card-header bg-primary-subtle py-2 d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-primary">
                                    <i class="bi bi-cash-stack me-1"></i> Historical Financial Overview &amp; Previous Outstandings
                                </span>
                                <span class="badge bg-primary text-white">Opening Balances</span>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label for="total_cost" class="form-label fw-semibold">
                                            Total Previous Treatment Cost <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white">PKR</span>
                                            <input type="number" step="0.01" min="0" id="total_cost" name="total_cost"
                                                value="{{ old('total_cost', '0.00') }}"
                                                class="form-control fw-bold @error('total_cost') is-invalid @enderror" required>
                                        </div>
                                        <div class="form-text small">Total fee agreed or charged for past visit.</div>
                                        @error('total_cost') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label for="paid_amount" class="form-label fw-semibold">
                                            Amount Paid in the Past
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white">PKR</span>
                                            <input type="number" step="0.01" min="0" id="paid_amount" name="paid_amount"
                                                value="{{ old('paid_amount', '0.00') }}"
                                                class="form-control text-success fw-bold @error('paid_amount') is-invalid @enderror">
                                        </div>
                                        <div class="form-text small">Amount the patient paid at that time.</div>
                                        @error('paid_amount') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Remaining Historical Due</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light">PKR</span>
                                            <input type="text" id="calculated_due_display" class="form-control bg-light fw-bold text-danger fs-6"
                                                value="0.00" readonly>
                                        </div>
                                        <div class="form-text small text-danger fw-semibold" id="due_status_hint">
                                            Will be tracked in Historical Outstandings.
                                        </div>
                                    </div>
                                </div>

                                {{-- Payment Method for initial paid amount --}}
                                <div class="row g-3 mt-1" id="initial_payment_details_row">
                                    <div class="col-md-6">
                                        <label for="payment_method" class="form-label small fw-semibold text-secondary">Initial Payment Method</label>
                                        <select name="payment_method" id="payment_method" class="form-select form-select-sm">
                                            <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                                            <option value="bank" {{ old('payment_method') === 'bank' ? 'selected' : '' }}>Bank Transfer</option>
                                            <option value="card" {{ old('payment_method') === 'card' ? 'selected' : '' }}>Credit / Debit Card</option>
                                            <option value="online" {{ old('payment_method') === 'online' ? 'selected' : '' }}>Online / JazzCash / EasyPaisa</option>
                                            <option value="other" {{ old('payment_method') === 'other' ? 'selected' : '' }}>Other</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="payment_reference" class="form-label small fw-semibold text-secondary">Payment Reference / Receipt #</label>
                                        <input type="text" id="payment_reference" name="payment_reference" value="{{ old('payment_reference') }}"
                                            class="form-control form-control-sm" placeholder="e.g. Old Receipt #1042, Cash Book Entry">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('patient-histories.index') }}" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-check-lg me-1"></i> Save Historical Patient Record
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

{{-- Interactive JavaScript for Instant Search & Calculation --}}
<script>
    const catalogPatients = @json($patients);

    // Patient Type Switch
    const typeExistingRadio = document.getElementById('type_existing');
    const typeNewRadio = document.getElementById('type_new');
    const existingSection = document.getElementById('existing_patient_section');
    const newSection = document.getElementById('new_patient_section');

    function togglePatientType() {
        if (typeNewRadio.checked) {
            existingSection.style.display = 'none';
            newSection.style.display = 'block';
            document.getElementById('patient_id').value = '';
        } else {
            existingSection.style.display = 'block';
            newSection.style.display = 'none';
        }
    }

    typeExistingRadio.addEventListener('change', togglePatientType);
    typeNewRadio.addEventListener('change', togglePatientType);

    // Patient Search Dropdown
    const patientSearchInput = document.getElementById('history_patient_search_input');
    const patientDropdown = document.getElementById('history_patient_dropdown');
    const patientIdInput = document.getElementById('patient_id');
    const clearPatientBtn = document.getElementById('clear_history_patient_btn');

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
            patientDropdown.innerHTML = `
                <div class="p-3 text-center text-muted">
                    <i class="bi bi-person-x fs-4 d-block mb-1"></i>
                    No existing patient found matching "<strong>${escapeHtml(queryText)}</strong>".
                    <div class="small mt-1 text-primary">Switch to "Register New Historical Patient" above.</div>
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
                <a href="javascript:void(0)" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between p-2 history-patient-dropdown-item" data-id="${p.id}">
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

        patientDropdown.querySelectorAll('.history-patient-dropdown-item').forEach(item => {
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
        patientDropdown.style.display = 'none';
        clearPatientBtn.style.display = 'inline-block';
    }

    function clearPatient() {
        patientIdInput.value = '';
        patientSearchInput.value = '';
        clearPatientBtn.style.display = 'none';
        patientSearchInput.focus();
    }

    patientSearchInput.addEventListener('input', function() { searchPatients(this.value); });
    patientSearchInput.addEventListener('focus', function() { searchPatients(this.value); });
    clearPatientBtn.addEventListener('click', clearPatient);

    document.addEventListener('click', function(e) {
        const wrapper = document.getElementById('history-patient-search-wrapper');
        if (wrapper && !wrapper.contains(e.target)) {
            patientDropdown.style.display = 'none';
        }
    });

    // Financial Calculation
    const totalCostInput = document.getElementById('total_cost');
    const paidAmountInput = document.getElementById('paid_amount');
    const calculatedDueDisplay = document.getElementById('calculated_due_display');
    const dueStatusHint = document.getElementById('due_status_hint');

    function recalculateDue() {
        const cost = parseFloat(totalCostInput.value) || 0;
        const paid = parseFloat(paidAmountInput.value) || 0;
        const due = Math.max(0, cost - paid);

        calculatedDueDisplay.value = due.toFixed(2);

        if (due <= 0) {
            calculatedDueDisplay.className = 'form-control bg-light fw-bold text-success fs-6';
            dueStatusHint.textContent = 'Account fully settled (PKR 0.00 outstanding).';
            dueStatusHint.className = 'form-text small text-success fw-semibold';
        } else {
            calculatedDueDisplay.className = 'form-control bg-light fw-bold text-danger fs-6';
            dueStatusHint.textContent = `PKR ${due.toFixed(2)} will be tracked in Historical Outstandings.`;
            dueStatusHint.className = 'form-text small text-danger fw-semibold';
        }
    }

    totalCostInput.addEventListener('input', recalculateDue);
    paidAmountInput.addEventListener('input', recalculateDue);

    function escapeHtml(string) {
        const div = document.createElement('div');
        div.innerText = string || '';
        return div.innerHTML;
    }

    // Initial run
    recalculateDue();
</script>

@endsection
