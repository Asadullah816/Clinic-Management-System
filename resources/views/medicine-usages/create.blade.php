@extends('layouts.app')

@section('title', 'Record Product Usage')

@section('content')

    <div class="card shadow-sm" style="max-width: 820px;">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Record Medicine / Product Usage</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('medicine-usages.store') }}">
                @csrf

                @if (request('redirect') === 'patient')
                    <input type="hidden" name="redirect" value="patient">
                @elseif (request('redirect') === 'medicine')
                    <input type="hidden" name="redirect" value="medicine">
                @endif

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="patient_id" class="form-label">Patient <span class="text-danger">*</span></label>
                        <select name="patient_id" id="patient_id"
                            class="form-select @error('patient_id') is-invalid @enderror" required>
                            <option value="">-- Select patient --</option>
                            @foreach ($patients as $p)
                                <option value="{{ $p->id }}"
                                    {{ (string) old('patient_id', $patientId) === (string) $p->id ? 'selected' : '' }}>
                                    {{ $p->patient_number }} &mdash; {{ $p->full_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('patient_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="treatment_id" class="form-label">Treatment</label>
                        <select name="treatment_id" id="treatment_id"
                            class="form-select @error('treatment_id') is-invalid @enderror">
                            <option value="">-- No treatment (general use) --</option>
                            @foreach ($treatments as $treatment)
                                <option value="{{ $treatment->id }}"
                                    {{ (string) old('treatment_id') === (string) $treatment->id ? 'selected' : '' }}>
                                    {{ $treatment->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">Optional — which treatment was this product used during?</div>
                        @error('treatment_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="medicine_id" class="form-label">Product <span class="text-danger">*</span></label>
                        <select name="medicine_id" id="medicine_id"
                            class="form-select @error('medicine_id') is-invalid @enderror" required>
                            <option value="">-- Select product --</option>
                            @foreach ($medicines as $m)
                                <option value="{{ $m->id }}" data-stock="{{ $m->stock_quantity }}"
                                    data-expired="{{ $m->isExpired() ? '1' : '0' }}"
                                    {{ (string) old('medicine_id', $medicineId) === (string) $m->id ? 'selected' : '' }}>
                                    {{ $m->name }} (Stock: {{ $m->stock_quantity }}
                                    {{ $m->unit }}){{ $m->isExpired() ? ' — EXPIRED' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text" id="stock-hint"></div>
                        @error('medicine_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                        <input type="number" step="1" min="1" id="quantity" name="quantity"
                            value="{{ old('quantity', 1) }}" class="form-control @error('quantity') is-invalid @enderror"
                            required>
                        @error('quantity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="usage_date" class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="date" id="usage_date" name="usage_date"
                            value="{{ old('usage_date', now()->toDateString()) }}"
                            class="form-control @error('usage_date') is-invalid @enderror" required>
                        @error('usage_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea id="notes" name="notes" rows="2" class="form-control @error('notes') is-invalid @enderror"
                        placeholder="e.g. Applied to affected areas before laser session">{{ old('notes') }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="alert alert-info py-2 small">
                    <i class="bi bi-info-circle me-1"></i>
                    Saving this record automatically decreases the product's stock.
                    Deleting the record later restores it.
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Record Usage</button>
                    <a href="{{ route('medicine-usages.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        var medicineSelect = document.getElementById('medicine_id');
        var quantityInput = document.getElementById('quantity');
        var stockHint = document.getElementById('stock-hint');

        function updateMedicineInfo() {
            var option = medicineSelect.options[medicineSelect.selectedIndex];

            quantityInput.removeAttribute('max');

            if (!option || !option.value) {
                stockHint.className = 'form-text';
                stockHint.textContent = '';
                return;
            }

            var stock = parseInt(option.dataset.stock, 10);
            quantityInput.setAttribute('max', stock);

            if (option.dataset.expired === '1') {
                stockHint.className = 'form-text text-danger fw-semibold';
                stockHint.textContent = 'This product is EXPIRED — it cannot be used on patients.';
            } else {
                stockHint.className = 'form-text';
                stockHint.textContent = 'Current stock: ' + stock + '. Maximum usable: ' + stock + '.';
            }
        }

        medicineSelect.addEventListener('change', updateMedicineInfo);
        updateMedicineInfo();
    </script>

@endsection
