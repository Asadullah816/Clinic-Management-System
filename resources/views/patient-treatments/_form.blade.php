{{-- Expects: $patientTreatment (null on create), $patients, $treatments, $patientId, $treatmentId, $price --}}

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="patient_id" class="form-label">Patient <span class="text-danger">*</span></label>
        <select name="patient_id" id="patient_id" class="form-select @error('patient_id') is-invalid @enderror" required>
            <option value="">-- Select patient --</option>
            @foreach ($patients as $p)
                <option value="{{ $p->id }}"
                    {{ (string) old('patient_id', $patientTreatment->patient_id ?? $patientId) === (string) $p->id ? 'selected' : '' }}>
                    {{ $p->patient_number }} &mdash; {{ $p->full_name }}
                </option>
            @endforeach
        </select>
        @error('patient_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label for="treatment_id" class="form-label">Treatment <span class="text-danger">*</span></label>
        <select name="treatment_id" id="treatment_id" class="form-select @error('treatment_id') is-invalid @enderror"
            required>
            <option value="">-- Select treatment --</option>
            @foreach ($treatments as $t)
                <option value="{{ $t->id }}" data-price="{{ $t->price }}"
                    {{ (string) old('treatment_id', $patientTreatment->treatment_id ?? $treatmentId) === (string) $t->id ? 'selected' : '' }}>
                    {{ $t->name }}
                </option>
            @endforeach
        </select>
        @error('treatment_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label for="treatment_date" class="form-label">Treatment Date <span class="text-danger">*</span></label>
        <input type="date" id="treatment_date" name="treatment_date"
            value="{{ old('treatment_date', $patientTreatment?->treatment_date?->format('Y-m-d') ?? now()->toDateString()) }}"
            class="form-control @error('treatment_date') is-invalid @enderror" required>
        @error('treatment_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4 mb-3">
        <label for="price" class="form-label">Price <span class="text-danger">*</span></label>
        <input type="number" step="0.01" min="0" id="price" name="price"
            value="{{ old('price', $price) }}" class="form-control @error('price') is-invalid @enderror" required>
        <div class="form-text">Auto-fills from the selected treatment — editable per patient.</div>
        @error('price')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4 mb-3">
        <label for="discount" class="form-label">Discount</label>
        <input type="number" step="0.01" min="0" id="discount" name="discount"
            value="{{ old('discount', $patientTreatment->discount ?? 0) }}"
            class="form-control @error('discount') is-invalid @enderror">
        @error('discount')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-8">
        <div class="alert alert-primary py-2 mb-0 d-flex justify-content-between align-items-center">
            <span class="fw-semibold">Total (Price &minus; Discount):</span>
            <span class="fs-5 fw-bold" id="total-preview">0.00</span>
        </div>
    </div>
</div>

@if (! $patientTreatment)
    <div class="card bg-light border-0 mb-3">
        <div class="card-body py-3 px-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="card-subtitle text-primary fw-semibold mb-0">
                    <i class="bi bi-credit-card me-1"></i> Payment &amp; Billing
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
                        Paid Amount <span class="text-danger">*</span>
                    </label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">$</span>
                        <input type="number" step="0.01" min="0" id="paid_amount" name="paid_amount"
                            value="{{ old('paid_amount') }}"
                            placeholder="0.00"
                            class="form-control @error('paid_amount') is-invalid @enderror">
                    </div>
                    <div class="form-text small" id="paid-hint">Defaults to full amount. Edit for partial/due.</div>
                    @error('paid_amount')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4 mb-3 mb-md-0">
                    <label class="form-label small fw-semibold text-secondary">Remaining Due</label>
                    <div class="form-control form-control-sm bg-white d-flex justify-content-between align-items-center">
                        <span class="fw-bold" id="due-preview">0.00</span>
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
    </div>
@endif

<div class="mb-3">
    <label for="notes" class="form-label">Notes</label>
    <textarea id="notes" name="notes" rows="2" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $patientTreatment->notes ?? '') }}</textarea>
    @error('notes')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<script>
    var treatmentSelect = document.getElementById('treatment_id');
    var priceInput = document.getElementById('price');
    var discountInput = document.getElementById('discount');
    var totalPreview = document.getElementById('total-preview');
    var paidInput = document.getElementById('paid_amount');
    var duePreview = document.getElementById('due-preview');
    var statusBadge = document.getElementById('payment-status-badge');
    var dueHint = document.getElementById('due-hint');
    var btnFullPaid = document.getElementById('btn-full-paid');
    var btnFullDue = document.getElementById('btn-full-due');
    var paymentMethodSelect = document.getElementById('payment_method');

    var userCustomizedPaid = false;

    function updateTotalAndDue(syncPaid = false) {
        var price = parseFloat(priceInput.value) || 0;
        var discount = parseFloat(discountInput.value) || 0;
        var total = Math.max(0, price - discount);
        totalPreview.textContent = total.toFixed(2);

        if (paidInput) {
            if (syncPaid || (!userCustomizedPaid && (!paidInput.value || paidInput.value === ''))) {
                paidInput.value = total > 0 ? total.toFixed(2) : '0.00';
            }

            var paid = parseFloat(paidInput.value) || 0;
            var due = Math.max(0, total - paid);
            duePreview.textContent = due.toFixed(2);

            if (due === 0) {
                statusBadge.className = 'badge bg-success';
                statusBadge.textContent = 'Paid';
                dueHint.textContent = 'Invoice will be settled in full.';
                if (paymentMethodSelect) paymentMethodSelect.disabled = false;
            } else if (paid > 0 && due > 0) {
                statusBadge.className = 'badge bg-warning text-dark';
                statusBadge.textContent = 'Partial Due';
                dueHint.textContent = 'Partial payment of ' + paid.toFixed(2) + ' recorded. Due: ' + due.toFixed(2);
                if (paymentMethodSelect) paymentMethodSelect.disabled = false;
            } else {
                statusBadge.className = 'badge bg-danger';
                statusBadge.textContent = 'Unpaid';
                dueHint.textContent = 'Full amount due. Invoice marked as pending.';
                if (paymentMethodSelect) paymentMethodSelect.disabled = true;
            }
        }
    }

    if (paidInput) {
        paidInput.addEventListener('input', function() {
            userCustomizedPaid = true;
            updateTotalAndDue(false);
        });

        if (btnFullPaid) {
            btnFullPaid.addEventListener('click', function() {
                userCustomizedPaid = false;
                updateTotalAndDue(true);
            });
        }

        if (btnFullDue) {
            btnFullDue.addEventListener('click', function() {
                userCustomizedPaid = true;
                paidInput.value = '0.00';
                updateTotalAndDue(false);
            });
        }
    }

    // Picking a treatment fills in its catalog price
    treatmentSelect.addEventListener('change', function() {
        var option = treatmentSelect.options[treatmentSelect.selectedIndex];
        if (option && option.dataset.price) {
            priceInput.value = option.dataset.price;
            updateTotalAndDue(!userCustomizedPaid);
        }
    });

    priceInput.addEventListener('input', function() { updateTotalAndDue(!userCustomizedPaid); });
    discountInput.addEventListener('input', function() { updateTotalAndDue(!userCustomizedPaid); });

    // Initial check: if already has input (e.g. from validation error back)
    if (paidInput && paidInput.value && paidInput.value.trim() !== '') {
        userCustomizedPaid = true;
    }
    updateTotalAndDue(false);
</script>
