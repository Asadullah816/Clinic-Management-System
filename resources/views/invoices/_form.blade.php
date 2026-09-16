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
        <label for="subtotal" class="form-label">Subtotal <span class="text-danger">*</span></label>
        <input type="number" step="0.01" min="0" id="subtotal" name="subtotal"
            value="{{ old('subtotal', $invoice->subtotal ?? '') }}"
            class="form-control @error('subtotal') is-invalid @enderror" required>
        <div class="form-text">The total charge for this visit/treatment.</div>
        @error('subtotal')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4 mb-3">
        <label for="discount" class="form-label">Discount</label>
        <input type="number" step="0.01" min="0" id="discount" name="discount"
            value="{{ old('discount', $invoice->discount ?? 0) }}"
            class="form-control @error('discount') is-invalid @enderror">
        @error('discount')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4 mb-3 d-flex align-items-end">
        <div class="alert alert-primary py-2 w-100 mb-0 d-flex justify-content-between align-items-center">
            <span class="fw-semibold">Total:</span>
            <span class="fs-5 fw-bold" id="total-preview">0.00</span>
        </div>
    </div>
</div>

@if (isset($invoice) && $invoice->paid_amount > 0)
    <div class="alert alert-warning py-2">
        <i class="bi bi-exclamation-triangle me-1"></i>
        Already paid: <strong>{{ number_format($invoice->paid_amount, 2) }}</strong>.
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
    var totalPreview = document.getElementById('total-preview');

    function updateTotal() {
        var subtotal = parseFloat(subtotalInput.value) || 0;
        var discount = parseFloat(discountInput.value) || 0;
        var total = subtotal - discount;
        totalPreview.textContent = (total > 0 ? total : 0).toFixed(2);
    }

    subtotalInput.addEventListener('input', updateTotal);
    discountInput.addEventListener('input', updateTotal);
    updateTotal();
</script>
