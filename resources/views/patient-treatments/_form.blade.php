{{-- Expects: $patientTreatment (null on create), $patients, $treatments, $medicines, $patientId, $treatmentId, $price --}}

@php
    $initialSelectedMedicines = [];
    if (old('medicines')) {
        foreach (old('medicines') as $oldMed) {
            $medId = (int) ($oldMed['id'] ?? 0);
            $found = collect($medicines)->firstWhere('id', $medId);
            if ($found) {
                $initialSelectedMedicines[] = [
                    'id'             => $medId,
                    'name'           => $found['name'],
                    'generic_name'   => $found['generic_name'],
                    'category'       => $found['category'],
                    'unit'           => $found['unit'] ?? 'unit',
                    'image_url'      => $found['image_url'],
                    'stock_quantity' => (int) $found['stock_quantity'],
                    'unit_price'     => (float) ($oldMed['unit_price'] ?? $found['selling_price']),
                    'quantity'       => (int) ($oldMed['quantity'] ?? 1),
                ];
            }
        }
    } elseif (isset($patientTreatment) && $patientTreatment && $patientTreatment->treatmentMedicines) {
        foreach ($patientTreatment->treatmentMedicines as $tm) {
            $medId = (int) $tm->medicine_id;
            $found = collect($medicines)->firstWhere('id', $medId);
            $initialSelectedMedicines[] = [
                'id'             => $medId,
                'name'           => $tm->medicine->name ?? ($found['name'] ?? 'Product #' . $medId),
                'generic_name'   => $tm->medicine->generic_name ?? ($found['generic_name'] ?? ''),
                'category'       => $tm->medicine->category->name ?? ($found['category'] ?? 'General'),
                'unit'           => $tm->medicine->unit ?? ($found['unit'] ?? 'unit'),
                'image_url'      => $tm->medicine->image_url ?? ($found['image_url'] ?? null),
                // While editing, available stock includes what was already reserved for this treatment
                'stock_quantity' => (int) (($tm->medicine->stock_quantity ?? 0) + $tm->quantity),
                'unit_price'     => (float) $tm->unit_price,
                'quantity'       => (int) $tm->quantity,
            ];
        }
    }
@endphp

<div class="row mb-3">
    <div class="col-md-5 mb-3 mb-md-0">
        <label for="patient_id" class="form-label fw-semibold">Patient <span class="text-danger">*</span></label>
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

    <div class="col-md-4 mb-3 mb-md-0">
        <label for="treatment_id" class="form-label fw-semibold">Treatment Procedure <span class="text-danger">*</span></label>
        <select name="treatment_id" id="treatment_id" class="form-select @error('treatment_id') is-invalid @enderror" required>
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

{{-- ================= SECTION 1: SEPARATE TREATMENT PRICING ================= --}}
<div class="card mb-4 border shadow-sm">
    <div class="card-header bg-primary-subtle py-2 d-flex justify-content-between align-items-center">
        <span class="fw-bold text-primary">
            <i class="bi bi-activity me-1"></i> 1. Treatment Procedure Pricing
        </span>
        <span class="badge bg-primary text-white">Procedure Only</span>
    </div>
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-4 mb-3 mb-md-0">
                <label for="price" class="form-label fw-semibold">Procedure Fee (PKR) <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-white">PKR</span>
                    <input type="number" step="0.01" min="0" id="price" name="price"
                        value="{{ old('price', $price ?? 0) }}" class="form-control @error('price') is-invalid @enderror" required>
                </div>
                <div class="form-text small">Auto-fills from catalog, editable per patient.</div>
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

                <div class="form-text small">Discount strictly applies to treatment fee.</div>
                @error('discount')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
                @error('discount_percentage')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label fw-semibold text-secondary">Procedure Net</label>
                <div class="p-2 border rounded bg-light d-flex justify-content-between align-items-center">
                    <span class="text-muted small">Fee &minus; Discount:</span>
                    <span class="fs-5 fw-bold text-primary" id="treatment-net-preview">PKR 0.00</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ================= SECTION 2: MEDICINES & PRODUCTS (SEPARATED) ================= --}}
<div class="card mb-4 border shadow-sm">
    <div class="card-header bg-success-subtle py-2 d-flex justify-content-between align-items-center">
        <span class="fw-bold text-success">
            <i class="bi bi-capsule me-1"></i> 2. Prescribed Products &amp; Medicines
        </span>
        <span class="badge bg-success text-white" id="selected-medicines-count-badge">0 items selected</span>
    </div>
    <div class="card-body">
        @error('medicines')
            <div class="alert alert-danger py-2 mb-3">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $message }}
            </div>
        @enderror

        <!-- Medicine Search with Dropdown -->
        <div class="position-relative mb-3" id="medicine-search-wrapper">
            <label class="form-label fw-semibold mb-1">
                <i class="bi bi-search text-success me-1"></i> Search &amp; Add Products
            </label>
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                <input type="text" id="medicine-search-input" class="form-control"
                    placeholder="Search by product name, generic name, or category..." autocomplete="off">
                <button class="btn btn-outline-secondary" type="button" id="clear-search-btn" style="display: none;">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="form-text small">Type product name to search catalog. Click on an item to add it to the treatment. Stock will be auto-calculated.</div>

            <!-- Search Dropdown Results Menu -->
            <div id="medicine-search-dropdown" class="list-group position-absolute w-100 shadow-lg rounded-3 border mt-1"
                style="max-height: 320px; overflow-y: auto; z-index: 1050; display: none; background: #ffffff;">
            </div>
        </div>

        <!-- Selected Medicines Table -->
        <div class="table-responsive border rounded-3 bg-white mb-3">
            <table class="table table-hover align-middle mb-0" id="selected-medicines-table">
                <thead class="table-light">
                    <tr>
                        <th style="width: 36%;">Product / Medicine</th>
                        <th style="width: 14%;" class="text-center">Available Stock</th>
                        <th style="width: 16%;">Unit Price (PKR)</th>
                        <th style="width: 14%;">Quantity</th>
                        <th style="width: 15%;" class="text-end">Line Total (PKR)</th>
                        <th style="width: 5%;" class="text-center"><i class="bi bi-trash"></i></th>
                    </tr>
                </thead>
                <tbody id="selected-medicines-tbody">
                    <!-- Populated dynamically via JavaScript -->
                </tbody>
            </table>
        </div>

        <!-- Medicines Pricing Sub-calculation (Separated Discount) -->
        <div class="row align-items-center bg-light p-3 rounded border">
            <div class="col-md-4 mb-2 mb-md-0">
                <div class="small text-muted fw-semibold">Medicines Subtotal:</div>
                <div class="fs-5 fw-bold text-dark" id="medicines-subtotal-preview">PKR 0.00</div>
            </div>
            <div class="col-md-4 mb-2 mb-md-0">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label small fw-semibold text-secondary mb-0">Medicines Discount</label>
                    <div class="btn-group btn-group-sm" role="group" aria-label="Medicine Discount Mode">
                        <input type="radio" class="btn-check" name="medicine_discount_type" id="mdt_type_fixed" value="fixed"
                            {{ old('medicine_discount_type', 'fixed') === 'fixed' ? 'checked' : '' }} autocomplete="off">
                        <label class="btn btn-outline-success py-0 px-2 small" for="mdt_type_fixed">Fixed (PKR)</label>

                        <input type="radio" class="btn-check" name="medicine_discount_type" id="mdt_type_percentage" value="percentage"
                            {{ old('medicine_discount_type') === 'percentage' ? 'checked' : '' }} autocomplete="off">
                        <label class="btn btn-outline-success py-0 px-2 small" for="mdt_type_percentage">% Off</label>
                    </div>
                </div>

                <!-- Fixed Price Medicine Discount Input -->
                <div id="wrapper_med_discount_fixed">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white">PKR</span>
                        <input type="number" step="0.01" min="0" id="medicine_discount" name="medicine_discount"
                            value="{{ old('medicine_discount', $patientTreatment->medicine_discount ?? 0) }}"
                            class="form-control @error('medicine_discount') is-invalid @enderror" placeholder="0.00">
                    </div>
                </div>

                <!-- Percentage Medicine Discount Input -->
                <div id="wrapper_med_discount_percentage" style="display: none;">
                    <div class="input-group input-group-sm">
                        <input type="number" step="0.01" min="0" max="100" id="medicine_discount_percentage" name="medicine_discount_percentage"
                            value="{{ old('medicine_discount_percentage') }}"
                            class="form-control @error('medicine_discount_percentage') is-invalid @enderror" placeholder="0 - 100">
                        <span class="input-group-text bg-white">%</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-1">
                        <span class="badge bg-light text-success border" id="mdt_calc_badge">= PKR 0.00 off</span>
                        <span class="text-muted small">0% &ndash; 100%</span>
                    </div>
                </div>

                @error('medicine_discount')
                    <div class="invalid-feedback d-block small">{{ $message }}</div>
                @enderror
                @error('medicine_discount_percentage')
                    <div class="invalid-feedback d-block small">{{ $message }}</div>
                @enderror
                <div class="form-text small">Separated discount strictly for medicines.</div>
            </div>
            <div class="col-md-4 text-md-end">
                <div class="small text-muted fw-semibold">Medicines Net Total:</div>
                <div class="fs-5 fw-bold text-success" id="medicines-net-preview">PKR 0.00</div>
            </div>
        </div>
    </div>
</div>

{{-- ================= SECTION 3: BILLING & GRAND TOTAL SUMMARY ================= --}}
<div class="card mb-4 border shadow-sm">
    <div class="card-header bg-dark text-white py-2 d-flex justify-content-between align-items-center">
        <span class="fw-bold">
            <i class="bi bi-receipt me-1"></i> 3. Grand Total &amp; Billing Summary
        </span>
        <span class="badge bg-light text-dark">Separated Breakdown</span>
    </div>
    <div class="card-body">
        <div class="row mb-3 g-3">
            <div class="col-md-6">
                <div class="border rounded p-3 h-100 bg-light">
                    <h6 class="text-secondary fw-bold mb-2">Cost Breakdown</h6>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span><i class="bi bi-activity text-primary me-1"></i> Procedure Net:</span>
                        <span class="fw-bold text-primary" id="bd-t-net">PKR 0.00</span>
                    </div>
                    <div class="d-flex justify-content-between text-muted small ps-3 mb-2">
                        <span>Fee: <span id="bd-t-price">PKR 0.00</span> &minus; Disc: <span id="bd-t-disc">PKR 0.00</span></span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-1 border-top pt-2">
                        <span><i class="bi bi-capsule text-success me-1"></i> Medicines Net:</span>
                        <span class="fw-bold text-success" id="bd-m-net">PKR 0.00</span>
                    </div>
                    <div class="d-flex justify-content-between text-muted small ps-3">
                        <span>Subtotal: <span id="bd-m-sub">PKR 0.00</span> &minus; Disc: <span id="bd-m-disc">PKR 0.00</span></span>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="border rounded p-3 h-100 bg-primary text-white text-center d-flex flex-column justify-content-center">
                    <span class="text-white-50 text-uppercase fw-semibold small tracking-wide">Grand Total Payable</span>
                    <div class="display-6 fw-bold my-1" id="grand-total-preview">PKR 0.00</div>
                    <div class="small text-white-50">Procedure Net + Medicines Net</div>
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
    <label for="notes" class="form-label fw-semibold">Treatment Notes / Prescription Instructions</label>
    <textarea id="notes" name="notes" rows="2" class="form-control @error('notes') is-invalid @enderror"
        placeholder="Add any specific clinical notes, patient instructions, or dosages...">{{ old('notes', $patientTreatment->notes ?? '') }}</textarea>
    @error('notes')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

{{-- ================= JAVASCRIPT LOGIC ================= --}}
<script>
    // All available active medicines from database
    const catalogMedicines = @json($medicines);

    // Initial selected medicines (from old input or existing treatment)
    let selectedMedicines = @json($initialSelectedMedicines);

    // DOM Elements
    const treatmentSelect = document.getElementById('treatment_id');
    const priceInput = document.getElementById('price');
    const discountInput = document.getElementById('discount');
    const dtTypeFixed = document.getElementById('dt_type_fixed');
    const dtTypePercentage = document.getElementById('dt_type_percentage');
    const wrapperDiscountFixed = document.getElementById('wrapper_discount_fixed');
    const wrapperDiscountPercentage = document.getElementById('wrapper_discount_percentage');
    const discountPercentageInput = document.getElementById('discount_percentage');
    const dtCalcBadge = document.getElementById('dt_calc_badge');
    const treatmentNetPreview = document.getElementById('treatment-net-preview');

    const medicineSearchInput = document.getElementById('medicine-search-input');
    const medicineSearchDropdown = document.getElementById('medicine-search-dropdown');
    const clearSearchBtn = document.getElementById('clear-search-btn');

    const selectedMedicinesTbody = document.getElementById('selected-medicines-tbody');
    const selectedMedicinesCountBadge = document.getElementById('selected-medicines-count-badge');
    const medicinesSubtotalPreview = document.getElementById('medicines-subtotal-preview');
    const medicineDiscountInput = document.getElementById('medicine_discount');
    const mdtTypeFixed = document.getElementById('mdt_type_fixed');
    const mdtTypePercentage = document.getElementById('mdt_type_percentage');
    const wrapperMedDiscountFixed = document.getElementById('wrapper_med_discount_fixed');
    const wrapperMedDiscountPercentage = document.getElementById('wrapper_med_discount_percentage');
    const medDiscountPercentageInput = document.getElementById('medicine_discount_percentage');
    const mdtCalcBadge = document.getElementById('mdt_calc_badge');
    const medicinesNetPreview = document.getElementById('medicines-net-preview');

    const bdTPrice = document.getElementById('bd-t-price');
    const bdTDisc = document.getElementById('bd-t-disc');
    const bdTNet = document.getElementById('bd-t-net');
    const bdMSub = document.getElementById('bd-m-sub');
    const bdMDisc = document.getElementById('bd-m-disc');
    const bdMNet = document.getElementById('bd-m-net');
    const grandTotalPreview = document.getElementById('grand-total-preview');

    const paidInput = document.getElementById('paid_amount');
    const duePreview = document.getElementById('due-preview');
    const statusBadge = document.getElementById('payment-status-badge');
    const dueHint = document.getElementById('due-hint');
    const btnFullPaid = document.getElementById('btn-full-paid');
    const btnFullDue = document.getElementById('btn-full-due');
    const paymentMethodSelect = document.getElementById('payment_method');

    let userCustomizedPaid = (paidInput && paidInput.value && paidInput.value.trim() !== '');

    // Render medicines table
    function renderMedicinesTable() {
        selectedMedicinesTbody.innerHTML = '';

        if (selectedMedicines.length === 0) {
            selectedMedicinesTbody.innerHTML = `
                <tr id="no-medicines-row">
                    <td colspan="6" class="text-center py-4 text-muted">
                        <i class="bi bi-capsule fs-3 d-block text-secondary mb-1"></i>
                        No medicines or products selected for this treatment.
                        <div class="small">Use the search box above to add prescribed products.</div>
                    </td>
                </tr>
            `;
            selectedMedicinesCountBadge.textContent = '0 items selected';
        } else {
            selectedMedicinesCountBadge.textContent = selectedMedicines.length + ' ' + (selectedMedicines.length === 1 ? 'item' : 'items');

            selectedMedicines.forEach((med, index) => {
                const tr = document.createElement('tr');
                const lineTotal = (med.quantity * med.unit_price).toFixed(2);

                const imageHtml = med.image_url
                    ? `<img src="${med.image_url}" alt="${med.name}" class="rounded border object-fit-cover me-2 flex-shrink-0" style="width: 38px; height: 38px;">`
                    : `<div class="rounded border bg-light d-flex align-items-center justify-content-center me-2 flex-shrink-0" style="width: 38px; height: 38px;"><i class="bi bi-capsule text-primary"></i></div>`;

                const stockBadgeClass = med.stock_quantity > 10 ? 'bg-success' : (med.stock_quantity > 0 ? 'bg-warning text-dark' : 'bg-danger');

                tr.innerHTML = `
                    <td>
                        <div class="d-flex align-items-center">
                            ${imageHtml}
                            <div class="text-truncate" style="max-width: 250px;">
                                <div class="fw-semibold text-dark text-truncate">${med.name}</div>
                                <div class="small text-muted text-truncate">${med.generic_name || med.category || ''}</div>
                            </div>
                        </div>
                        <input type="hidden" name="medicines[${index}][id]" value="${med.id}">
                    </td>
                    <td class="text-center">
                        <span class="badge ${stockBadgeClass} px-2 py-1">
                            ${med.stock_quantity} ${med.unit}
                        </span>
                    </td>
                    <td>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white">PKR</span>
                            <input type="number" step="0.01" min="0"
                                class="form-control form-control-sm text-end med-price-input"
                                name="medicines[${index}][unit_price]"
                                value="${parseFloat(med.unit_price).toFixed(2)}"
                                data-index="${index}">
                        </div>
                    </td>
                    <td>
                        <input type="number" min="1" max="${med.stock_quantity}"
                            class="form-control form-control-sm text-center med-qty-input ${med.quantity > med.stock_quantity ? 'is-invalid' : ''}"
                            name="medicines[${index}][quantity]"
                            value="${med.quantity}"
                            data-index="${index}">
                    </td>
                    <td class="text-end fw-bold text-dark">
                        PKR <span id="line-total-${index}">${lineTotal}</span>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 remove-med-btn" data-index="${index}" title="Remove">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                `;

                selectedMedicinesTbody.appendChild(tr);
            });
        }

        // Attach event listeners to table inputs
        document.querySelectorAll('.med-price-input').forEach(input => {
            input.addEventListener('input', function() {
                const idx = parseInt(this.dataset.index, 10);
                const val = parseFloat(this.value) || 0;
                selectedMedicines[idx].unit_price = Math.max(0, val);
                document.getElementById('line-total-' + idx).textContent = (selectedMedicines[idx].quantity * selectedMedicines[idx].unit_price).toFixed(2);
                recalculateAll();
            });
        });

        document.querySelectorAll('.med-qty-input').forEach(input => {
            input.addEventListener('input', function() {
                const idx = parseInt(this.dataset.index, 10);
                let val = parseInt(this.value, 10);
                const maxStock = selectedMedicines[idx].stock_quantity;

                if (isNaN(val) || val < 1) val = 1;
                if (val > maxStock) {
                    this.classList.add('is-invalid');
                } else {
                    this.classList.remove('is-invalid');
                }

                selectedMedicines[idx].quantity = val;
                document.getElementById('line-total-' + idx).textContent = (selectedMedicines[idx].quantity * selectedMedicines[idx].unit_price).toFixed(2);
                recalculateAll();
            });
        });

        document.querySelectorAll('.remove-med-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const idx = parseInt(this.dataset.index, 10);
                selectedMedicines.splice(idx, 1);
                renderMedicinesTable();
                recalculateAll();
            });
        });
    }

    // Medicine search filter logic
    function searchMedicines(query) {
        if (!query || query.trim() === '') {
            medicineSearchDropdown.style.display = 'none';
            medicineSearchDropdown.innerHTML = '';
            clearSearchBtn.style.display = 'none';
            return;
        }

        clearSearchBtn.style.display = 'inline-block';
        const q = query.toLowerCase().trim();

        const filtered = catalogMedicines.filter(m => {
            const nameMatch = m.name && m.name.toLowerCase().includes(q);
            const genericMatch = m.generic_name && m.generic_name.toLowerCase().includes(q);
            const catMatch = m.category && m.category.toLowerCase().includes(q);
            return nameMatch || genericMatch || catMatch;
        });

        if (filtered.length === 0) {
            medicineSearchDropdown.innerHTML = `
                <div class="p-3 text-center text-muted">
                    <i class="bi bi-search mb-1 d-block"></i>
                    No products found matching "<strong>${escapeHtml(query)}</strong>"
                </div>
            `;
            medicineSearchDropdown.style.display = 'block';
            return;
        }

        let html = '';
        filtered.forEach(med => {
            const isSelected = selectedMedicines.some(sm => sm.id === med.id);
            const isOutOfStock = med.stock_quantity <= 0;
            const isExpired = med.is_expired;
            const disabled = isOutOfStock || isExpired;

            let badge = '';
            if (isExpired) {
                badge = '<span class="badge bg-danger">Expired</span>';
            } else if (isOutOfStock) {
                badge = '<span class="badge bg-danger">Out of Stock (0)</span>';
            } else if (med.stock_quantity <= 10) {
                badge = `<span class="badge bg-warning text-dark">${med.stock_quantity} ${med.unit} left</span>`;
            } else {
                badge = `<span class="badge bg-success-subtle text-success border border-success-subtle">${med.stock_quantity} ${med.unit}</span>`;
            }

            const imageHtml = med.image_url
                ? `<img src="${med.image_url}" alt="${med.name}" class="rounded border object-fit-cover me-2 flex-shrink-0" style="width: 44px; height: 44px;">`
                : `<div class="rounded border bg-light d-flex align-items-center justify-content-center me-2 flex-shrink-0" style="width: 44px; height: 44px;"><i class="bi bi-capsule text-primary fs-5"></i></div>`;

            html += `
                <a href="javascript:void(0)" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between p-2 search-result-item ${disabled ? 'opacity-50 text-muted disabled' : ''}"
                    data-id="${med.id}">
                    <div class="d-flex align-items-center flex-grow-1 overflow-hidden me-2">
                        ${imageHtml}
                        <div class="text-truncate">
                            <div class="fw-bold text-dark text-truncate">${med.name}</div>
                            <div class="small text-muted text-truncate">
                                ${med.generic_name ? med.generic_name + ' &bull; ' : ''}<span class="badge bg-light text-secondary border">${med.category}</span>
                            </div>
                        </div>
                    </div>
                    <div class="text-end flex-shrink-0">
                        <div class="fw-bold text-primary mb-1">PKR ${med.selling_price.toFixed(2)}</div>
                        <div class="d-flex align-items-center justify-content-end gap-1">
                            ${badge}
                            ${isSelected ? '<span class="badge bg-info text-white"><i class="bi bi-check-lg"></i> Added</span>' : ''}
                        </div>
                    </div>
                </a>
            `;
        });

        medicineSearchDropdown.innerHTML = html;
        medicineSearchDropdown.style.display = 'block';

        // Attach click handlers
        medicineSearchDropdown.querySelectorAll('.search-result-item:not(.disabled)').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                const id = parseInt(this.dataset.id, 10);
                addMedicineToTreatment(id);
                medicineSearchInput.value = '';
                medicineSearchDropdown.style.display = 'none';
                clearSearchBtn.style.display = 'none';
            });
        });
    }

    // Add medicine to selection
    function addMedicineToTreatment(medId) {
        const med = catalogMedicines.find(m => m.id === medId);
        if (!med) return;

        if (med.is_expired) {
            alert('Cannot add "' + med.name + '" because it is expired.');
            return;
        }

        if (med.stock_quantity <= 0) {
            alert('Cannot add "' + med.name + '" because it is out of stock.');
            return;
        }

        const existing = selectedMedicines.find(sm => sm.id === medId);
        if (existing) {
            if (existing.quantity < existing.stock_quantity) {
                existing.quantity += 1;
            } else {
                alert('Maximum available stock reached for "' + med.name + '" (' + existing.stock_quantity + ' ' + (existing.unit || 'units') + ').');
            }
        } else {
            selectedMedicines.push({
                id: med.id,
                name: med.name,
                generic_name: med.generic_name,
                category: med.category,
                unit: med.unit,
                image_url: med.image_url,
                stock_quantity: med.stock_quantity,
                unit_price: med.selling_price,
                quantity: 1,
            });
        }

        renderMedicinesTable();
        recalculateAll();
    }

    // Helper to escape HTML in search query
    function escapeHtml(string) {
        const div = document.createElement('div');
        div.innerText = string;
        return div.innerHTML;
    }

    // Mode switching handlers for Procedure Discount
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

    // Mode switching handlers for Medicines Discount
    function syncMedicineDiscountMode() {
        if (mdtTypePercentage && mdtTypePercentage.checked) {
            wrapperMedDiscountFixed.style.display = 'none';
            wrapperMedDiscountPercentage.style.display = 'block';
        } else {
            wrapperMedDiscountFixed.style.display = 'block';
            wrapperMedDiscountPercentage.style.display = 'none';
        }
    }

    if (mdtTypeFixed && mdtTypePercentage) {
        mdtTypeFixed.addEventListener('change', function() {
            if (this.checked) {
                let mSub = 0;
                selectedMedicines.forEach(m => { mSub += (m.quantity * m.unit_price); });
                const pct = Math.max(0, parseFloat(medDiscountPercentageInput.value) || 0);
                if (pct > 0 && mSub > 0) {
                    medicineDiscountInput.value = Math.round(mSub * (pct / 100) * 100 / 100).toFixed(2);
                }
                syncMedicineDiscountMode();
                recalculateAll(!userCustomizedPaid);
            }
        });
        mdtTypePercentage.addEventListener('change', function() {
            if (this.checked) {
                let mSub = 0;
                selectedMedicines.forEach(m => { mSub += (m.quantity * m.unit_price); });
                const flat = Math.max(0, parseFloat(medicineDiscountInput.value) || 0);
                if (flat > 0 && mSub > 0 && (!medDiscountPercentageInput.value || parseFloat(medDiscountPercentageInput.value) === 0)) {
                    medDiscountPercentageInput.value = Math.min(100, Math.round((flat / mSub * 100) * 10) / 10);
                }
                syncMedicineDiscountMode();
                recalculateAll(!userCustomizedPaid);
            }
        });
    }

    // Recalculate all separated pricing, totals, and remaining due
    function recalculateAll(syncPaid = false) {
        // 1. Treatment procedure pricing (Separated)
        const tPrice = Math.max(0, parseFloat(priceInput.value) || 0);
        let tDisc = 0;

        if (dtTypePercentage && dtTypePercentage.checked) {
            const pct = Math.min(100, Math.max(0, parseFloat(discountPercentageInput.value) || 0));
            tDisc = Math.round(tPrice * (pct / 100) * 100) / 100;
            if (dtCalcBadge) {
                dtCalcBadge.textContent = '= PKR ' + tDisc.toFixed(2) + ' off';
            }
            if (discountInput) {
                discountInput.value = tDisc.toFixed(2);
            }
        } else {
            tDisc = Math.max(0, parseFloat(discountInput.value) || 0);
        }

        const tNet = Math.max(0, tPrice - tDisc);

        treatmentNetPreview.textContent = 'PKR ' + tNet.toFixed(2);
        bdTPrice.textContent = 'PKR ' + tPrice.toFixed(2);
        bdTDisc.textContent = 'PKR ' + tDisc.toFixed(2);
        bdTNet.textContent = 'PKR ' + tNet.toFixed(2);

        // 2. Medicines pricing (Separated)
        let mSubtotal = 0;
        selectedMedicines.forEach(med => {
            mSubtotal += (med.quantity * med.unit_price);
        });

        let mDisc = 0;
        if (mdtTypePercentage && mdtTypePercentage.checked) {
            const pct = Math.min(100, Math.max(0, parseFloat(medDiscountPercentageInput.value) || 0));
            mDisc = Math.round(mSubtotal * (pct / 100) * 100) / 100;
            if (mdtCalcBadge) {
                mdtCalcBadge.textContent = '= PKR ' + mDisc.toFixed(2) + ' off';
            }
            if (medicineDiscountInput) {
                medicineDiscountInput.value = mDisc.toFixed(2);
            }
        } else {
            mDisc = Math.max(0, parseFloat(medicineDiscountInput.value) || 0);
        }

        const mNet = Math.max(0, mSubtotal - mDisc);

        medicinesSubtotalPreview.textContent = 'PKR ' + mSubtotal.toFixed(2);
        medicinesNetPreview.textContent = 'PKR ' + mNet.toFixed(2);
        bdMSub.textContent = 'PKR ' + mSubtotal.toFixed(2);
        bdMDisc.textContent = 'PKR ' + mDisc.toFixed(2);
        bdMNet.textContent = 'PKR ' + mNet.toFixed(2);

        // 3. Grand Total = Treatment Procedure Net + Medicines Net
        const grandTotal = Math.max(0, tNet + mNet);
        grandTotalPreview.textContent = 'PKR ' + grandTotal.toFixed(2);

        // 4. Payment & Due calculations (if present)
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

    // Treatment catalog select change handler
    treatmentSelect.addEventListener('change', function() {
        const option = treatmentSelect.options[treatmentSelect.selectedIndex];
        if (option && option.dataset.price) {
            priceInput.value = parseFloat(option.dataset.price).toFixed(2);
            recalculateAll(!userCustomizedPaid);
        }
    });

    // Inputs listeners for treatment price and discounts
    priceInput.addEventListener('input', function() { recalculateAll(!userCustomizedPaid); });
    discountInput.addEventListener('input', function() { recalculateAll(!userCustomizedPaid); });
    if (discountPercentageInput) {
        discountPercentageInput.addEventListener('input', function() { recalculateAll(!userCustomizedPaid); });
    }
    medicineDiscountInput.addEventListener('input', function() { recalculateAll(!userCustomizedPaid); });
    if (medDiscountPercentageInput) {
        medDiscountPercentageInput.addEventListener('input', function() { recalculateAll(!userCustomizedPaid); });
    }

    // Initialize discount display modes
    syncTreatmentDiscountMode();
    syncMedicineDiscountMode();

    // Payment listeners
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

    // Medicine search input handlers
    medicineSearchInput.addEventListener('input', function() {
        searchMedicines(this.value);
    });

    medicineSearchInput.addEventListener('focus', function() {
        if (this.value && this.value.trim() !== '') {
            searchMedicines(this.value);
        }
    });

    clearSearchBtn.addEventListener('click', function() {
        medicineSearchInput.value = '';
        medicineSearchDropdown.style.display = 'none';
        clearSearchBtn.style.display = 'none';
        medicineSearchInput.focus();
    });

    // Close dropdown on outside click
    document.addEventListener('click', function(e) {
        const wrapper = document.getElementById('medicine-search-wrapper');
        if (wrapper && !wrapper.contains(e.target)) {
            medicineSearchDropdown.style.display = 'none';
        }
    });

    // Initial render
    renderMedicinesTable();
    recalculateAll(false);
</script>
