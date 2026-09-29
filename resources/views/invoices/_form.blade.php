{{-- Expects: $invoice (null on create), $patients, $patientId (optional preselect) --}}

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="patient_id" class="form-label">Patient <span class="text-danger">*</span></label>
        <select name="patient_id" id="patient_id" class="form-select @error('patient_id') is-invalid @enderror" required>
            <option value="">-- Select patient --</option>
            @foreach ($patients as $p)
                <option value="{{ $p->id }}"
                    {{ (string) old('patient_id', $invoice->patient_id ?? $patientId) === (string) $p->id ? 'selected' : '' }}>
                    {{ $p->patient_number }} &mdash; {{ $p->full_name }} ({{ $p->phone }})
                </option>
            @endforeach
        </select>
        @error('patient_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label for="invoice_date" class="form-label">Invoice Date <span class="text-danger">*</span></label>
        <input type="date" id="invoice_date" name="invoice_date"
            value="{{ old('invoice_date', $invoice?->invoice_date?->format('Y-m-d') ?? now()->toDateString()) }}"
            class="form-control @error('invoice_date') is-invalid @enderror" required>
        @error('invoice_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label for="subtotal" class="form-label">Subtotal (PKR) <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text">PKR</span>
            <input type="number" step="0.01" min="0" id="subtotal" name="subtotal"
                value="{{ old('subtotal', $invoice->subtotal ?? '') }}"
                class="form-control @error('subtotal') is-invalid @enderror" placeholder="0.00" required>
        </div>
        <div class="form-text">The total charge for this visit/treatment.</div>
        @error('subtotal')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4 mb-3">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <label class="form-label mb-0 fw-semibold">Discount</label>
            <div class="btn-group btn-group-sm" role="group" aria-label="Invoice Discount Mode">
                <input type="radio" class="btn-check" name="discount_type" id="inv_dt_fixed" value="fixed"
                    {{ old('discount_type', 'fixed') === 'fixed' ? 'checked' : '' }} autocomplete="off">
                <label class="btn btn-outline-primary py-0 px-2 small" for="inv_dt_fixed">Fixed (PKR)</label>

                <input type="radio" class="btn-check" name="discount_type" id="inv_dt_percentage" value="percentage"
                    {{ old('discount_type') === 'percentage' ? 'checked' : '' }} autocomplete="off">
                <label class="btn btn-outline-primary py-0 px-2 small" for="inv_dt_percentage">% Off</label>
            </div>
        </div>

        <!-- Fixed Price Discount Input -->
        <div id="inv-fixed-discount-wrap">
            <div class="input-group">
                <span class="input-group-text bg-white">PKR</span>
                <input type="number" step="0.01" min="0" id="discount" name="discount"
                    value="{{ old('discount', $invoice->discount ?? 0) }}"
                    class="form-control @error('discount') is-invalid @enderror" placeholder="0.00">
            </div>
        </div>

        <!-- Percentage Discount Input -->
        <div id="inv-percent-discount-wrap" style="display: none;">
            <div class="input-group">
                <input type="number" step="0.01" min="0" max="100" id="discount_percentage" name="discount_percentage"
                    value="{{ old('discount_percentage') }}"
                    class="form-control @error('discount_percentage') is-invalid @enderror" placeholder="0 - 100">
                <span class="input-group-text bg-white">%</span>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-1">
                <span class="badge bg-light text-primary border" id="inv_pct_calc_badge">= PKR 0.00 off</span>
                <span class="text-muted small">0% &ndash; 100%</span>
            </div>
        </div>

        @error('discount')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
        @error('discount_percentage')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4 mb-3 d-flex align-items-end">
        <div class="alert alert-primary py-2 w-100 mb-0 d-flex justify-content-between align-items-center">
            <span class="fw-semibold">Total:</span>
            <span class="fs-5 fw-bold" id="total-preview">PKR 0.00</span>
        </div>
    </div>
</div>

@if (isset($invoice) && $invoice->paid_amount > 0)
    <div class="alert alert-warning py-2">
        <i class="bi bi-exclamation-triangle me-1"></i>
        Already paid: <strong>PKR {{ number_format($invoice->paid_amount, 2) }}</strong>.
        The new total cannot be less than this amount.
    </div>
@endif

<div class="mb-3">
    <label for="notes" class="form-label">Notes</label>
    <textarea id="notes" name="notes" rows="2" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $invoice->notes ?? '') }}</textarea>
    @error('notes')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<script>
    var subtotalInput = document.getElementById('subtotal');
    var discountInput = document.getElementById('discount');
    var discountPercentageInput = document.getElementById('discount_percentage');
    var totalPreview = document.getElementById('total-preview');
    var invDtFixed = document.getElementById('inv_dt_fixed');
    var invDtPercentage = document.getElementById('inv_dt_percentage');
    var invFixedWrap = document.getElementById('inv-fixed-discount-wrap');
    var invPercentWrap = document.getElementById('inv-percent-discount-wrap');
    var invPctCalcBadge = document.getElementById('inv_pct_calc_badge');

    function syncDiscountMode() {
        if (invDtPercentage && invDtPercentage.checked) {
            invFixedWrap.style.display = 'none';
            invPercentWrap.style.display = 'block';
        } else {
            invFixedWrap.style.display = 'block';
            invPercentWrap.style.display = 'none';
        }
        updateTotal();
    }

    if (invDtFixed && invDtPercentage) {
        invDtFixed.addEventListener('change', function() {
            if (this.checked) {
                var subtotal = parseFloat(subtotalInput.value) || 0;
                var pct = parseFloat(discountPercentageInput.value) || 0;
                if (pct > 0 && subtotal > 0) {
                    discountInput.value = Math.round(subtotal * (pct / 100) * 100 / 100).toFixed(2);
                }
                syncDiscountMode();
            }
        });
        invDtPercentage.addEventListener('change', function() {
            if (this.checked) {
                var subtotal = parseFloat(subtotalInput.value) || 0;
                var flat = parseFloat(discountInput.value) || 0;
                if (flat > 0 && subtotal > 0 && (!discountPercentageInput.value || parseFloat(discountPercentageInput.value) === 0)) {
                    discountPercentageInput.value = Math.min(100, Math.round((flat / subtotal * 100) * 10) / 10);
                }
                syncDiscountMode();
            }
        });
    }

    function updateTotal() {
        var subtotal = parseFloat(subtotalInput.value) || 0;
        var discount = 0;

        if (invDtPercentage && invDtPercentage.checked) {
            var pct = Math.min(100, Math.max(0, parseFloat(discountPercentageInput.value) || 0));
            discount = Math.round(subtotal * (pct / 100) * 100) / 100;
            if (invPctCalcBadge) {
                invPctCalcBadge.textContent = '= PKR ' + discount.toFixed(2) + ' off';
            }
            if (discountInput) {
                discountInput.value = discount.toFixed(2);
            }
        } else {
            discount = parseFloat(discountInput.value) || 0;
        }

        var total = Math.max(0, subtotal - discount);
        totalPreview.textContent = 'PKR ' + total.toFixed(2);
    }

    subtotalInput.addEventListener('input', updateTotal);
    discountInput.addEventListener('input', updateTotal);
    if (discountPercentageInput) {
        discountPercentageInput.addEventListener('input', updateTotal);
    }

    syncDiscountMode();
</script>
