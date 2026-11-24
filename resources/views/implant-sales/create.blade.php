@extends('layouts.app')

@section('title', 'New Dental Implant Sale / Dispatch')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0 fw-bold text-dark">
                <i class="bi bi-cart-plus text-primary me-2"></i>Record Dental Implant Sale / Dispatch
            </h4>
            <p class="text-muted small mb-0">Supply implants to external doctors or issue for in-house clinic surgery.</p>
        </div>
        <a href="{{ route('implant-sales.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Sales
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger py-2 px-3 mb-3 rounded-3">
            <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Please check the following issues:</div>
            <ul class="mb-0 small ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('implant-sales.store') }}" id="implant-sale-form">
        @csrf

        {{-- 1. Buyer & Basic Information --}}
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-person-check text-primary me-2"></i>1. Doctor / Client Selection</h6>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="implant_buyer_id" class="form-label fw-semibold">
                            Select Doctor / Clinic <span class="text-danger">*</span>
                        </label>
                        <select name="implant_buyer_id" id="implant_buyer_id" class="form-select @error('implant_buyer_id') is-invalid @enderror" required>
                            <option value="">-- Choose Purchasing Doctor or Self-Use --</option>
                            @foreach ($buyers as $b)
                                <option value="{{ $b->id }}"
                                    {{ old('implant_buyer_id', $preselectedBuyerId ?? '') == $b->id ? 'selected' : '' }}>
                                    {{ $b->displayName() }}
                                    @if ($b->phone) (Ph: {{ $b->phone }}) @endif
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text small d-flex justify-content-between align-items-center mt-1">
                            <span>Includes external doctors and internal clinic self-use.</span>
                            <a href="{{ route('implant-buyers.create') }}" target="_blank" class="text-primary text-decoration-none">
                                + Register New Doctor
                            </a>
                        </div>
                        @error('implant_buyer_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label for="sale_date" class="form-label fw-semibold">Dispatch / Sale Date <span class="text-danger">*</span></label>
                        <input type="date" id="sale_date" name="sale_date"
                            value="{{ old('sale_date', date('Y-m-d')) }}"
                            class="form-control @error('sale_date') is-invalid @enderror" required>
                        @error('sale_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label for="patient_reference" class="form-label fw-semibold">Patient Reference / Notes</label>
                        <input type="text" id="patient_reference" name="patient_reference"
                            value="{{ old('patient_reference') }}"
                            class="form-control @error('patient_reference') is-invalid @enderror"
                            placeholder="e.g. For Pt. Zahid (Tooth #46)">
                        @error('patient_reference')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. Implant Line Items --}}
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-pin-angle text-info me-2"></i>2. Implant Products &amp; Quantities</h6>
                <button type="button" class="btn btn-sm btn-outline-primary" id="btn-add-item">
                    <i class="bi bi-plus-lg me-1"></i> Add Another Implant
                </button>
            </div>
            <div class="card-body p-4">
                <div id="items-container" class="d-flex flex-column gap-3">
                    {{-- Default Row 0 --}}
                    <div class="item-row p-3 rounded border bg-light position-relative">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-secondary-subtle text-secondary item-row-num">Implant #1</span>
                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-item py-0 px-2" style="display: none;">
                                <i class="bi bi-trash"></i> Remove
                            </button>
                        </div>
                        <div class="row g-2 align-items-end">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Select Implant Model &amp; Specs <span class="text-danger">*</span></label>
                                <select name="items[0][implant_id]" class="form-select form-select-sm implant-select" required>
                                    <option value="">-- Choose Implant from Stock --</option>
                                    @foreach ($implants as $imp)
                                        <option value="{{ $imp->id }}"
                                            data-price="{{ $imp->sale_price }}"
                                            data-stock="{{ $imp->stock_quantity }}"
                                            data-code="{{ $imp->item_code }}"
                                            data-spec="{{ $imp->fullSpecification() }}"
                                            {{ $imp->isOutOfStock() ? 'disabled' : '' }}>
                                            {{ $imp->fullSpecification() }}
                                            — PKR {{ number_format($imp->sale_price, 2) }}
                                            [{{ $imp->isOutOfStock() ? 'OUT OF STOCK' : 'Stock: ' . $imp->stock_quantity . ' left' }}]
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-semibold">Quantity <span class="text-danger">*</span></label>
                                <input type="number" name="items[0][quantity]" class="form-control form-control-sm item-qty"
                                    min="1" value="1" required>
                                <div class="form-text small item-stock-hint" style="font-size: 0.72rem;">Available: --</div>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-semibold">Unit Price (PKR) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" name="items[0][unit_price]" class="form-control form-control-sm item-price" required>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-semibold">Line Total</label>
                                <input type="text" class="form-control form-control-sm bg-white fw-bold item-line-total" readonly value="PKR 0.00">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. Billing Summary & Payment Settlement --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-wallet2 text-success me-2"></i>3. Financial Settlement &amp; Outstanding Dues</h6>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    {{-- Calculations Breakdown --}}
                    <div class="col-md-6 border-end">
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Subtotal (Implants Gross)</span>
                            <span class="fw-bold fs-6 text-dark" id="display-subtotal">PKR 0.00</span>
                        </div>
                        <div class="py-2 border-bottom">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label for="discount" class="text-muted small fw-semibold mb-0">Special Discount (PKR)</label>
                                <input type="number" step="0.01" min="0" name="discount" id="discount"
                                    value="{{ old('discount', 0) }}" class="form-control form-control-sm w-50 text-end">
                            </div>
                        </div>
                        <div class="d-flex justify-content-between py-3 border-bottom bg-light px-3 rounded mt-2">
                            <span class="fw-bold fs-5 text-dark">Grand Total Payable</span>
                            <span class="fw-bold fs-5 text-primary" id="display-total">PKR 0.00</span>
                        </div>
                    </div>

                    {{-- Immediate Payment Settlement --}}
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="paid_amount" class="form-label fw-semibold">Amount Paid Now (PKR)</label>
                            <input type="number" step="0.01" min="0" name="paid_amount" id="paid_amount"
                                value="{{ old('paid_amount', 0) }}" class="form-control form-control-lg fw-bold text-success">
                            <div class="d-flex gap-2 mt-2">
                                <button type="button" class="btn btn-sm btn-outline-success py-0 px-2" id="btn-pay-full">Pay Full Amount</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" id="btn-pay-zero">Leave on Due (Full Credit)</button>
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label for="payment_method" class="form-label small fw-semibold">Payment Method</label>
                                <select name="payment_method" id="payment_method" class="form-select form-select-sm">
                                    <option value="cash">Cash</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="online">Online / EasyPaisa</option>
                                    <option value="cheque">Cheque</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Remaining Due Balance</label>
                                <div class="form-control form-control-sm fw-bold bg-light" id="display-due">
                                    PKR 0.00
                                </div>
                            </div>
                        </div>

                        <div class="alert py-2 px-3 small d-flex align-items-center mb-0" id="due-status-alert">
                            <i class="bi bi-info-circle me-2"></i>
                            <span id="due-status-text">Enter products to calculate payment.</span>
                        </div>
                    </div>

                    <div class="col-12">
                        <label for="notes" class="form-label fw-semibold">Sale / Delivery Notes</label>
                        <textarea name="notes" id="notes" rows="2" class="form-control"
                            placeholder="e.g. Delivered via rider, surgeon collected in person, warranty details...">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('implant-sales.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold">
                        <i class="bi bi-check-lg me-1"></i> Save &amp; Generate Invoice
                    </button>
                </div>
            </div>
        </div>
    </form>

    {{-- JavaScript for Dynamic Items & Calculations --}}
    <script>
        const catalogImplants = @json($implants);
        let rowIndex = 1;

        const itemsContainer = document.getElementById('items-container');
        const btnAddItem = document.getElementById('btn-add-item');
        const discountInput = document.getElementById('discount');
        const paidInput = document.getElementById('paid_amount');
        const btnPayFull = document.getElementById('btn-pay-full');
        const btnPayZero = document.getElementById('btn-pay-zero');

        const displaySubtotal = document.getElementById('display-subtotal');
        const displayTotal = document.getElementById('display-total');
        const displayDue = document.getElementById('display-due');
        const dueAlert = document.getElementById('due-status-alert');
        const dueText = document.getElementById('due-status-text');

        function attachRowEvents(row) {
            const select = row.querySelector('.implant-select');
            const qtyInput = row.querySelector('.item-qty');
            const priceInput = row.querySelector('.item-price');
            const totalInput = row.querySelector('.item-line-total');
            const stockHint = row.querySelector('.item-stock-hint');
            const removeBtn = row.querySelector('.btn-remove-item');

            select.addEventListener('change', function() {
                const opt = select.options[select.selectedIndex];
                if (opt && opt.value) {
                    const price = parseFloat(opt.dataset.price || 0);
                    const stock = parseInt(opt.dataset.stock || 0, 10);
                    priceInput.value = price.toFixed(2);
                    qtyInput.max = stock;
                    stockHint.textContent = `Available: ${stock} units`;
                    if (stock <= 0) {
                        stockHint.className = 'form-text text-danger fw-bold';
                        stockHint.textContent = 'Out of Stock!';
                    } else if (stock <= 5) {
                        stockHint.className = 'form-text text-warning fw-semibold';
                    } else {
                        stockHint.className = 'form-text text-success';
                    }
                } else {
                    priceInput.value = '';
                    stockHint.textContent = 'Available: --';
                }
                recalculate();
            });

            qtyInput.addEventListener('input', recalculate);
            priceInput.addEventListener('input', recalculate);

            if (removeBtn) {
                removeBtn.addEventListener('click', function() {
                    row.remove();
                    reindexRows();
                    recalculate();
                });
            }
        }

        function reindexRows() {
            const rows = itemsContainer.querySelectorAll('.item-row');
            rows.forEach((r, idx) => {
                r.querySelector('.item-row-num').textContent = `Implant #${idx + 1}`;
                const removeBtn = r.querySelector('.btn-remove-item');
                if (removeBtn) {
                    removeBtn.style.display = rows.length > 1 ? 'inline-block' : 'none';
                }
            });
        }

        btnAddItem.addEventListener('click', function() {
            const newRow = document.createElement('div');
            newRow.className = 'item-row p-3 rounded border bg-light position-relative';

            let optionsHtml = '<option value="">-- Choose Implant from Stock --</option>';
            catalogImplants.forEach(imp => {
                const disabled = imp.stock_quantity <= 0 ? 'disabled' : '';
                const stockText = imp.stock_quantity <= 0 ? 'OUT OF STOCK' : `Stock: ${imp.stock_quantity} left`;
                optionsHtml += `<option value="${imp.id}" data-price="${imp.sale_price}" data-stock="${imp.stock_quantity}" ${disabled}>
                    ${escapeHtml(imp.brand ? imp.brand + ' ' : '')}${escapeHtml(imp.name)} (${imp.diameter ? 'Ø ' + imp.diameter : ''} ${imp.length ? '× L: ' + imp.length : ''}) — PKR ${parseFloat(imp.sale_price).toFixed(2)} [${stockText}]
                </option>`;
            });

            newRow.innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-secondary-subtle text-secondary item-row-num">Implant #${rowIndex + 1}</span>
                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-item py-0 px-2">
                        <i class="bi bi-trash"></i> Remove
                    </button>
                </div>
                <div class="row g-2 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Select Implant Model &amp; Specs <span class="text-danger">*</span></label>
                        <select name="items[${rowIndex}][implant_id]" class="form-select form-select-sm implant-select" required>
                            ${optionsHtml}
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Quantity <span class="text-danger">*</span></label>
                        <input type="number" name="items[${rowIndex}][quantity]" class="form-control form-control-sm item-qty" min="1" value="1" required>
                        <div class="form-text small item-stock-hint" style="font-size: 0.72rem;">Available: --</div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Unit Price (PKR) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" name="items[${rowIndex}][unit_price]" class="form-control form-control-sm item-price" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Line Total</label>
                        <input type="text" class="form-control form-control-sm bg-white fw-bold item-line-total" readonly value="PKR 0.00">
                    </div>
                </div>
            `;

            itemsContainer.appendChild(newRow);
            attachRowEvents(newRow);
            rowIndex++;
            reindexRows();
            recalculate();
        });

        function recalculate() {
            let subtotal = 0;

            itemsContainer.querySelectorAll('.item-row').forEach(row => {
                const qty = parseInt(row.querySelector('.item-qty').value || 0, 10);
                const price = parseFloat(row.querySelector('.item-price').value || 0);
                const lineTotal = Math.max(0, qty * price);
                row.querySelector('.item-line-total').value = 'PKR ' + lineTotal.toFixed(2);
                subtotal += lineTotal;
            });

            displaySubtotal.textContent = 'PKR ' + subtotal.toFixed(2);

            const discount = parseFloat(discountInput.value || 0);
            const total = Math.max(0, subtotal - discount);
            displayTotal.textContent = 'PKR ' + total.toFixed(2);

            let paid = parseFloat(paidInput.value || 0);
            if (paid > total) {
                paid = total;
                paidInput.value = total.toFixed(2);
            }

            const due = Math.max(0, total - paid);
            displayDue.textContent = 'PKR ' + due.toFixed(2);

            if (due <= 0 && total > 0) {
                displayDue.className = 'form-control form-control-sm fw-bold bg-success-subtle text-success';
                dueAlert.className = 'alert alert-success py-2 px-3 small d-flex align-items-center mb-0';
                dueText.textContent = 'Fully Paid: No outstanding balance will remain.';
            } else if (paid > 0) {
                displayDue.className = 'form-control form-control-sm fw-bold bg-warning-subtle text-warning-emphasis';
                dueAlert.className = 'alert alert-warning py-2 px-3 small d-flex align-items-center mb-0';
                dueText.textContent = `Partially Paid: PKR ${due.toFixed(2)} will remain on doctor outstanding ledger.`;
            } else if (total > 0) {
                displayDue.className = 'form-control form-control-sm fw-bold bg-danger-subtle text-danger';
                dueAlert.className = 'alert alert-danger py-2 px-3 small d-flex align-items-center mb-0';
                dueText.textContent = `Full Due: PKR ${due.toFixed(2)} recorded on doctor credit/outstanding.`;
            } else {
                displayDue.className = 'form-control form-control-sm fw-bold bg-light text-dark';
                dueAlert.className = 'alert alert-secondary py-2 px-3 small d-flex align-items-center mb-0';
                dueText.textContent = 'Select implant products to calculate invoice.';
            }
        }

        btnPayFull.addEventListener('click', function() {
            let subtotal = 0;
            itemsContainer.querySelectorAll('.item-row').forEach(row => {
                const qty = parseInt(row.querySelector('.item-qty').value || 0, 10);
                const price = parseFloat(row.querySelector('.item-price').value || 0);
                subtotal += (qty * price);
            });
            const discount = parseFloat(discountInput.value || 0);
            const total = Math.max(0, subtotal - discount);
            paidInput.value = total.toFixed(2);
            recalculate();
        });

        btnPayZero.addEventListener('click', function() {
            paidInput.value = '0.00';
            recalculate();
        });

        discountInput.addEventListener('input', recalculate);
        paidInput.addEventListener('input', recalculate);

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.innerText = text || '';
            return div.innerHTML;
        }

        // Attach initial row events
        itemsContainer.querySelectorAll('.item-row').forEach(attachRowEvents);
        reindexRows();
    </script>
@endsection
