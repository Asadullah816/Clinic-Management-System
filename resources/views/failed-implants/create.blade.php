@extends('layouts.app')

@section('title', 'Report Failed Dental Implant')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h4 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-x-octagon text-danger me-2"></i>Report Failed Dental Implant
                    </h4>
                    <p class="text-muted small mb-0">Record a clinical failure, dropped implant, or packaging defect. Calculated separately from regular sales.</p>
                </div>
                <a href="{{ route('failed-implants.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Failed Implants
                </a>
            </div>

            <div class="alert alert-warning border-0 shadow-sm d-flex align-items-start py-2 px-3 mb-3">
                <i class="bi bi-exclamation-triangle-fill text-warning fs-5 me-2 mt-1"></i>
                <div class="small">
                    <strong>Separate Financial Calculation:</strong> This record will be tallied in the separate Failed Implants ledger.
                    If the source is <em>Direct Inventory / Stock</em>, stock will automatically be reduced and logged under failed inventory transactions.
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('failed-implants.store') }}" id="failedImplantForm">
                        @csrf

                        {{-- Source Selection --}}
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Failure Origin / Source <span class="text-danger">*</span></label>
                                <select name="source" id="sourceSelect" class="form-select @error('source') is-invalid @enderror" required>
                                    <option value="stock" {{ old('source', $source) === 'stock' ? 'selected' : '' }}>
                                        Direct Inventory / Clinic Stock (Decrements stock)
                                    </option>
                                    <option value="sale" {{ old('source', $source) === 'sale' ? 'selected' : '' }}>
                                        From Sold Invoice / External Doctor
                                    </option>
                                    <option value="internal" {{ old('source', $source) === 'internal' ? 'selected' : '' }}>
                                        In-House Clinic Procedure (Dr Badshah Afsar)
                                    </option>
                                </select>
                                @error('source') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6" id="doctorGroup">
                                <label class="form-label fw-bold">Doctor / Clinic Buyer</label>
                                <select name="implant_buyer_id" id="buyerSelect" class="form-select @error('implant_buyer_id') is-invalid @enderror">
                                    <option value="">-- Select Doctor (Optional) --</option>
                                    @foreach ($buyers as $buyer)
                                        <option value="{{ $buyer->id }}"
                                            {{ old('implant_buyer_id', $selectedBuyer?->id ?? $buyerId) == $buyer->id ? 'selected' : '' }}>
                                            {{ $buyer->doctor_name }} {{ $buyer->clinic_hospital_name ? "({$buyer->clinic_hospital_name})" : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('implant_buyer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        {{-- Sale Reference (if from doctor sale) --}}
                        <div class="row g-3 mb-3" id="saleGroup">
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Related Sale Invoice (Optional)</label>
                                <select name="implant_sale_id" class="form-select @error('implant_sale_id') is-invalid @enderror">
                                    <option value="">-- No specific invoice linked --</option>
                                    @foreach ($sales as $sale)
                                        <option value="{{ $sale->id }}"
                                            {{ old('implant_sale_id', $selectedSale?->id ?? $saleId) == $sale->id ? 'selected' : '' }}>
                                            {{ $sale->sale_number }} - {{ $sale->buyer?->doctor_name }} ({{ $sale->sale_date->format('d M Y') }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('implant_sale_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        {{-- Implant Fixture & Date & Quantity --}}
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Implant Fixture <span class="text-danger">*</span></label>
                                <select name="implant_id" id="implantSelect" class="form-select @error('implant_id') is-invalid @enderror" required>
                                    <option value="">-- Choose Implant Model --</option>
                                    @foreach ($implants as $imp)
                                        <option value="{{ $imp->id }}"
                                            data-price="{{ $imp->purchase_price }}"
                                            data-stock="{{ $imp->stock_quantity }}"
                                            {{ old('implant_id', $selectedImplant?->id ?? $implantId) == $imp->id ? 'selected' : '' }}>
                                            {{ $imp->fullSpecification() }} (In Stock: {{ $imp->stock_quantity }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('implant_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold">Failure Date <span class="text-danger">*</span></label>
                                <input type="date" name="failure_date" class="form-control @error('failure_date') is-invalid @enderror"
                                    value="{{ old('failure_date', date('Y-m-d')) }}" required>
                                @error('failure_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold">Quantity <span class="text-danger">*</span></label>
                                <input type="number" name="quantity" id="quantityInput" class="form-control @error('quantity') is-invalid @enderror"
                                    value="{{ old('quantity', 1) }}" min="1" required>
                                @error('quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        {{-- Financial Loss Calculation --}}
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Unit Purchase / Valuation Cost (PKR) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="unit_cost" id="unitCostInput"
                                    class="form-control @error('unit_cost') is-invalid @enderror"
                                    value="{{ old('unit_cost', $selectedImplant?->purchase_price ?? 0) }}" required>
                                <span class="small text-muted">Cost price used for calculating separate financial write-off.</span>
                                @error('unit_cost') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Total Loss / Cost (PKR)</label>
                                <input type="text" id="totalCostDisplay" class="form-control bg-light fw-bold text-danger" readonly value="0.00">
                                <span class="small text-muted">Automatically calculated (Quantity × Unit Cost).</span>
                            </div>
                        </div>

                        <hr class="my-4">

                        {{-- Clinical Failure Details --}}
                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-bandaid text-danger me-2"></i>Clinical &amp; Defect Details</h6>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Failure Stage <span class="text-danger">*</span></label>
                                <select name="failure_stage" class="form-select @error('failure_stage') is-invalid @enderror" required>
                                    @foreach (\App\Models\FailedImplant::stages() as $key => $label)
                                        <option value="{{ $key }}" {{ old('failure_stage') === $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('failure_stage') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Failure Reason <span class="text-danger">*</span></label>
                                <select name="failure_reason" class="form-select @error('failure_reason') is-invalid @enderror" required>
                                    @foreach (\App\Models\FailedImplant::reasons() as $key => $label)
                                        <option value="{{ $key }}" {{ old('failure_reason') === $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('failure_reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tooth Number / Site</label>
                                <input type="text" name="tooth_number" class="form-control @error('tooth_number') is-invalid @enderror"
                                    placeholder="e.g. #11, #14, #36, #46 or Upper Left"
                                    value="{{ old('tooth_number') }}">
                                @error('tooth_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Patient Name (Optional)</label>
                                <input type="text" name="patient_name" class="form-control @error('patient_name') is-invalid @enderror"
                                    placeholder="Patient who received or was scheduled for the implant"
                                    value="{{ old('patient_name') }}">
                                @error('patient_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <hr class="my-4">

                        {{-- Warranty & Resolution --}}
                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-shield-check text-primary me-2"></i>Warranty &amp; Settlement Resolution</h6>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Warranty / Settlement Status <span class="text-danger">*</span></label>
                                <select name="warranty_status" class="form-select @error('warranty_status') is-invalid @enderror" required>
                                    @foreach (\App\Models\FailedImplant::warrantyStatuses() as $key => $label)
                                        <option value="{{ $key }}" {{ old('warranty_status', \App\Models\FailedImplant::WARRANTY_PENDING) === $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('warranty_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Replacement Implant Provided (If Any)</label>
                                <select name="replacement_implant_id" class="form-select @error('replacement_implant_id') is-invalid @enderror">
                                    <option value="">-- No Replacement Provided Yet --</option>
                                    @foreach ($implants as $rep)
                                        <option value="{{ $rep->id }}" {{ old('replacement_implant_id') == $rep->id ? 'selected' : '' }}>
                                            {{ $rep->fullSpecification() }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('replacement_implant_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Clinical Notes / Failure Description</label>
                            <textarea name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror"
                                placeholder="Describe bone condition, insertion torque, sterility issue, or manufacturer warranty claim notes...">{{ old('notes') }}</textarea>
                            @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('failed-implants.index') }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-danger px-4">
                                <i class="bi bi-check2-circle me-1"></i> Save Failure Record
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const implantSelect = document.getElementById('implantSelect');
            const unitCostInput = document.getElementById('unitCostInput');
            const quantityInput = document.getElementById('quantityInput');
            const totalCostDisplay = document.getElementById('totalCostDisplay');

            function updateTotal() {
                const qty = parseFloat(quantityInput.value) || 0;
                const cost = parseFloat(unitCostInput.value) || 0;
                const total = (qty * cost).toFixed(2);
                totalCostDisplay.value = 'PKR ' + parseFloat(total).toLocaleString('en-US', { minimumFractionDigits: 2 });
            }

            implantSelect.addEventListener('change', function () {
                const opt = this.options[this.selectedIndex];
                const price = opt.getAttribute('data-price');
                if (price && (!unitCostInput.value || unitCostInput.value === '0')) {
                    unitCostInput.value = parseFloat(price).toFixed(2);
                }
                updateTotal();
            });

            unitCostInput.addEventListener('input', updateTotal);
            quantityInput.addEventListener('input', updateTotal);
            updateTotal();
        });
    </script>
    @endpush
@endsection
