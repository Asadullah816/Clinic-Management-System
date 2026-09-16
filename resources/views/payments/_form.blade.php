{{-- Expects: $payment (null on create), $invoices (collection, may be empty), $lockedInvoice (Invoice or null) --}}

@if ($lockedInvoice)

    {{-- Locked mode: the invoice cannot be changed --}}
    <input type="hidden" name="invoice_id" value="{{ $lockedInvoice->id }}">
    <div class="row mb-3">
        <div class="col-md-4">
            <label class="form-label">Invoice</label>
            <input type="text" class="form-control" value="{{ $lockedInvoice->invoice_number }}" disabled>
        </div>
        <div class="col-md-4">
            <label class="form-label">Patient</label>
            <input type="text" class="form-control" value="{{ $lockedInvoice->patient->full_name }}" disabled>
        </div>
        <div class="col-md-4">
            <label class="form-label">Remaining Due</label>
            <input type="text" class="form-control fw-bold text-danger"
                value="{{ number_format($lockedInvoice->due_amount, 2) }}" disabled>
        </div>
    </div>
@else
    {{-- Dropdown mode: pick any invoice that still has a due amount --}}
    <div class="mb-3">
        <label for="invoice_id" class="form-label">Invoice <span class="text-danger">*</span></label>
        <select name="invoice_id" id="invoice_id" class="form-select @error('invoice_id') is-invalid @enderror"
            required>
            <option value="">-- Select invoice --</option>
            @foreach ($invoices as $inv)
                <option value="{{ $inv->id }}" data-patient="{{ $inv->patient->full_name }}"
                    data-due="{{ $inv->due_amount }}"
                    {{ (string) old('invoice_id') === (string) $inv->id ? 'selected' : '' }}>
                    {{ $inv->invoice_number }} &mdash; {{ $inv->patient->full_name }} (Due:
                    {{ number_format($inv->due_amount, 2) }})
                </option>
            @endforeach
        </select>
        <div class="form-text">Only invoices with an outstanding balance are listed.</div>
        @error('invoice_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label">Patient</label>
            <input type="text" class="form-control" id="patient-display" value="{{ old('invoice_id') ? '' : '—' }}"
                disabled>
        </div>
        <div class="col-md-6">
            <label class="form-label">Remaining Due</label>
            <input type="text" class="form-control" id="due-display" value="{{ old('invoice_id') ? '' : '—' }}"
                disabled>
        </div>
    </div>

@endif

<div class="row">
    <div class="col-md-4 mb-3">
        <label for="payment_date" class="form-label">Payment Date <span class="text-danger">*</span></label>
        <input type="date" id="payment_date" name="payment_date"
            value="{{ old('payment_date', $payment?->payment_date?->format('Y-m-d') ?? now()->toDateString()) }}"
            class="form-control @error('payment_date') is-invalid @enderror" required>
        @error('payment_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4 mb-3">
        <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
        <input type="number" step="0.01" min="0.01" id="amount" name="amount"
            value="{{ old('amount', $payment->amount ?? '') }}"
            class="form-control @error('amount') is-invalid @enderror" required>
        @error('amount')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4 mb-3">
        <label for="payment_method" class="form-label">Payment Method <span class="text-danger">*</span></label>
        <select name="payment_method" id="payment_method"
            class="form-select @error('payment_method') is-invalid @enderror" required>
            @foreach (\App\Models\Payment::methods() as $methodValue => $methodLabel)
                <option value="{{ $methodValue }}"
                    {{ old('payment_method', $payment->payment_method ?? 'cash') === $methodValue ? 'selected' : '' }}>
                    {{ $methodLabel }}
                </option>
            @endforeach
        </select>
        @error('payment_method')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="mb-3">
    <label for="reference" class="form-label">Reference</label>
    <input type="text" id="reference" name="reference" value="{{ old('reference', $payment->reference ?? '') }}"
        class="form-control @error('reference') is-invalid @enderror"
        placeholder="e.g. Receipt #, transaction ID, cheque number">
    @error('reference')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="notes" class="form-label">Notes</label>
    <textarea id="notes" name="notes" rows="2" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $payment->notes ?? '') }}</textarea>
    @error('notes')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

@if (!$lockedInvoice)
    <script>
        var invoiceSelect = document.getElementById('invoice_id');
        var patientDisplay = document.getElementById('patient-display');
        var dueDisplay = document.getElementById('due-display');
        var amountInput = document.getElementById('amount');

        function updateInvoiceInfo() {
            var option = invoiceSelect.options[invoiceSelect.selectedIndex];
            if (!option || !option.value) {
                patientDisplay.value = '—';
                dueDisplay.value = '—';
                amountInput.removeAttribute('max');
                return;
            }
            patientDisplay.value = option.dataset.patient;
            dueDisplay.value = parseFloat(option.dataset.due).toFixed(2);
            amountInput.setAttribute('max', parseFloat(option.dataset.due).toFixed(2));
        }

        invoiceSelect.addEventListener('change', updateInvoiceInfo);
        updateInvoiceInfo();
    </script>
@endif
