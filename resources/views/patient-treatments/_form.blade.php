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

    function updateTotal() {
        var price = parseFloat(priceInput.value) || 0;
        var discount = parseFloat(discountInput.value) || 0;
        var total = price - discount;
        totalPreview.textContent = (total > 0 ? total : 0).toFixed(2);
    }

    // Picking a treatment fills in its catalog price
    treatmentSelect.addEventListener('change', function() {
        var option = treatmentSelect.options[treatmentSelect.selectedIndex];
        if (option && option.dataset.price) {
            priceInput.value = option.dataset.price;
            updateTotal();
        }
    });

    priceInput.addEventListener('input', updateTotal);
    discountInput.addEventListener('input', updateTotal);
    updateTotal();
</script>
