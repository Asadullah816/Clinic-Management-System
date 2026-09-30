@extends('layouts.app')

@section('title', 'Record Laser Session')

@section('content')
    <div class="row justify-content-center">
        <div class="col-12 col-xl-10">
            <div class="card border shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 fw-bold" style="color: #0f172a;">Record Laser Treatment Session</h5>
                        <p class="text-muted small mb-0">Record procedure, laser fluence/shots parameters, and separate billing.</p>
                    </div>
                    <a href="{{ route('laser.sessions.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Back to Sessions
                    </a>
                </div>

                <div class="card-body p-4">
                    <form method="POST" action="{{ route('laser.sessions.store') }}" id="laserSessionForm">
                        @csrf

                        {{-- Section 1: Patient & Procedure --}}
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="fw-bold text-primary mb-0"><i class="bi bi-person-badge me-1"></i> 1. Patient &amp; Procedure</h6>
                            <a href="{{ route('laser.patients.create') }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                <i class="bi bi-plus-lg me-1"></i> Register New Patient
                            </a>
                        </div>

                        <div class="row g-3 mb-4 p-3 bg-light rounded-3 border">
                            <div class="col-md-6">
                                <label for="laser_patient_id" class="form-label fw-semibold">Select Laser Patient <span class="text-danger">*</span></label>
                                <select name="laser_patient_id" id="laser_patient_id" class="form-select @error('laser_patient_id') is-invalid @enderror" required>
                                    <option value="">-- Choose Patient --</option>
                                    @foreach ($patients as $pat)
                                        <option value="{{ $pat->id }}"
                                                data-skin="{{ $pat->skin_type }}"
                                                data-phone="{{ $pat->phone }}"
                                                {{ (old('laser_patient_id', $selectedPatient?->id) == $pat->id) ? 'selected' : '' }}>
                                            {{ $pat->full_name }} ({{ $pat->patient_number }}) - {{ $pat->phone }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('laser_patient_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="laser_treatment_id" class="form-label fw-semibold">Laser Procedure <span class="text-danger">*</span></label>
                                <select name="laser_treatment_id" id="laser_treatment_id" class="form-select @error('laser_treatment_id') is-invalid @enderror" required>
                                    <option value="">-- Choose Laser Procedure --</option>
                                    @foreach ($treatments as $treatment)
                                        <option value="{{ $treatment->id }}"
                                                data-price="{{ $treatment->price }}"
                                                data-area="{{ $treatment->body_area }}"
                                                {{ (old('laser_treatment_id', $treatmentId) == $treatment->id) ? 'selected' : '' }}>
                                            {{ $treatment->name }} &bull; PKR {{ number_format($treatment->price, 0) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('laser_treatment_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="session_date" class="form-label fw-semibold">Session Date <span class="text-danger">*</span></label>
                                <input type="date" name="session_date" id="session_date"
                                       class="form-control @error('session_date') is-invalid @enderror"
                                       value="{{ old('session_date', date('Y-m-d')) }}" required>
                                @error('session_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="session_number" class="form-label fw-semibold">Session Number <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white">#</span>
                                    <input type="number" min="1" name="session_number" id="session_number"
                                           class="form-control @error('session_number') is-invalid @enderror"
                                           value="{{ old('session_number', $nextSessionNumber) }}" required>
                                </div>
                                @error('session_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="total_sessions" class="form-label fw-semibold">Total Planned Sessions</label>
                                <input type="number" min="1" name="total_sessions" id="total_sessions"
                                       class="form-control @error('total_sessions') is-invalid @enderror"
                                       value="{{ old('total_sessions', 6) }}" placeholder="e.g. 6">
                                @error('total_sessions')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Section 2: Machine Settings & Clinical Parameters --}}
                        <h6 class="fw-bold text-primary mb-3"><i class="bi bi-sliders me-1"></i> 2. Machine &amp; Technical Parameters</h6>
                        <div class="row g-3 mb-4 p-3 bg-light rounded-3 border">
                            <div class="col-md-4">
                                <label for="laser_machine" class="form-label fw-semibold">Laser Machine / Technology</label>
                                <input type="text" name="laser_machine" id="laser_machine"
                                       class="form-control @error('laser_machine') is-invalid @enderror"
                                       value="{{ old('laser_machine', 'Diode Laser (808nm / 755nm / 1064nm)') }}"
                                       placeholder="e.g. Diode Triple Wave, Nd:YAG Q-Switch">
                                @error('laser_machine')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-2">
                                <label for="fluence" class="form-label fw-semibold">Fluence (J/cm²)</label>
                                <input type="text" name="fluence" id="fluence"
                                       class="form-control @error('fluence') is-invalid @enderror"
                                       value="{{ old('fluence') }}" placeholder="e.g. 18 J/cm²">
                                @error('fluence')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-2">
                                <label for="pulse_width" class="form-label fw-semibold">Pulse Width (ms)</label>
                                <input type="text" name="pulse_width" id="pulse_width"
                                       class="form-control @error('pulse_width') is-invalid @enderror"
                                       value="{{ old('pulse_width') }}" placeholder="e.g. 30 ms">
                                @error('pulse_width')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-2">
                                <label for="spot_size" class="form-label fw-semibold">Spot Size</label>
                                <input type="text" name="spot_size" id="spot_size"
                                       class="form-control @error('spot_size') is-invalid @enderror"
                                       value="{{ old('spot_size') }}" placeholder="e.g. 12x12 mm">
                                @error('spot_size')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-2">
                                <label for="pulses_count" class="form-label fw-semibold">Shots Fired</label>
                                <input type="number" min="0" name="pulses_count" id="pulses_count"
                                       class="form-control @error('pulses_count') is-invalid @enderror"
                                       value="{{ old('pulses_count') }}" placeholder="e.g. 1200">
                                @error('pulses_count')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="performed_by" class="form-label fw-semibold">Operator / Specialist</label>
                                <select name="performed_by" id="performed_by" class="form-select @error('performed_by') is-invalid @enderror">
                                    <option value="">Select Doctor / Laser Tech...</option>
                                    @foreach ($doctors as $doc)
                                        <option value="{{ $doc->id }}" {{ old('performed_by', auth()->id()) == $doc->id ? 'selected' : '' }}>
                                            {{ $doc->name }} ({{ ucfirst($doc->role) }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('performed_by')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="notes" class="form-label fw-semibold">Session Observations / Notes</label>
                                <input type="text" name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror"
                                       value="{{ old('notes') }}" placeholder="Skin tolerance, erythema, cooling level applied...">
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Section 3: Billing & Separate Discount --}}
                        <h6 class="fw-bold text-primary mb-3"><i class="bi bi-cash-coin me-1"></i> 3. Billing &amp; Separate Laser Discount</h6>
                        <div class="row g-3 mb-4 p-3 bg-light rounded-3 border">
                            {{-- Price --}}
                            <div class="col-md-4">
                                <label for="price" class="form-label fw-semibold">Standard Price (PKR) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white">PKR</span>
                                    <input type="number" step="0.01" min="0" name="price" id="price"
                                           class="form-control fw-bold @error('price') is-invalid @enderror"
                                           value="{{ old('price') }}" required>
                                </div>
                                @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Discount Type & Value --}}
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Laser Discount</label>
                                <div class="input-group">
                                    <select name="discount_type" id="discount_type" class="form-select" style="max-width: 90px;">
                                        <option value="fixed" {{ old('discount_type') === 'percentage' ? '' : 'selected' }}>PKR</option>
                                        <option value="percentage" {{ old('discount_type') === 'percentage' ? 'selected' : '' }}>%</option>
                                    </select>
                                    <input type="number" step="0.01" min="0" name="discount_input" id="discount_input"
                                           class="form-control" placeholder="0"
                                           value="{{ old('discount_percentage') ?: old('discount', 0) }}">
                                </div>
                                <input type="hidden" name="discount" id="discount_hidden" value="{{ old('discount', 0) }}">
                                <input type="hidden" name="discount_percentage" id="discount_percentage_hidden" value="{{ old('discount_percentage') }}">
                                @error('discount')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Net Total --}}
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Net Total Payable</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-primary text-white fw-bold">PKR</span>
                                    <input type="text" id="net_total_display" class="form-control fw-bold fs-5 bg-white text-primary" readonly value="0.00">
                                </div>
                            </div>

                            {{-- Payment Received --}}
                            <div class="col-md-4">
                                <label for="paid_amount" class="form-label fw-semibold">Amount Paid Now (PKR)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-success-subtle text-success fw-bold">PKR</span>
                                    <input type="number" step="0.01" min="0" name="paid_amount" id="paid_amount"
                                           class="form-control fw-bold text-success @error('paid_amount') is-invalid @enderror"
                                           value="{{ old('paid_amount') }}" placeholder="Defaults to Net Total">
                                </div>
                                <div class="form-text">Leave blank to mark as fully paid automatically.</div>
                                @error('paid_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Payment Method --}}
                            <div class="col-md-4">
                                <label for="payment_method" class="form-label fw-semibold">Payment Method</label>
                                <select name="payment_method" id="payment_method" class="form-select @error('payment_method') is-invalid @enderror">
                                    <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="card" {{ old('payment_method') === 'card' ? 'selected' : '' }}>Credit / Debit Card</option>
                                    <option value="bank" {{ old('payment_method') === 'bank' ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="online" {{ old('payment_method') === 'online' ? 'selected' : '' }}>Online / JazzCash / EasyPaisa</option>
                                    <option value="other" {{ old('payment_method') === 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('payment_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Payment Reference --}}
                            <div class="col-md-4">
                                <label for="payment_reference" class="form-label fw-semibold">Payment Reference / Txn #</label>
                                <input type="text" name="payment_reference" id="payment_reference"
                                       class="form-control @error('payment_reference') is-invalid @enderror"
                                       value="{{ old('payment_reference') }}" placeholder="e.g. Card slip # or Txn ID">
                                @error('payment_reference')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Calculation Summary Box --}}
                        <div class="alert alert-info py-2 px-3 d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <i class="bi bi-info-circle me-1"></i>
                                <span>A unique Laser Invoice (e.g. <code>LINV-XXXX</code>) and 80mm thermal receipt will be automatically generated upon saving.</span>
                            </div>
                            <div class="fw-bold" id="calculatedBalanceText">Balance Due: PKR 0.00</div>
                        </div>

                        {{-- Submit Buttons --}}
                        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                            <a href="{{ route('laser.sessions.index') }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4 fw-semibold shadow-sm">
                                <i class="bi bi-check-circle me-1"></i> Save &amp; Print Laser Receipt
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Interactive Calculations Script --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const treatmentSelect = document.getElementById('laser_treatment_id');
            const priceInput = document.getElementById('price');
            const discountTypeSelect = document.getElementById('discount_type');
            const discountInput = document.getElementById('discount_input');
            const discountHidden = document.getElementById('discount_hidden');
            const discountPercentHidden = document.getElementById('discount_percentage_hidden');
            const netTotalDisplay = document.getElementById('net_total_display');
            const paidAmountInput = document.getElementById('paid_amount');
            const calculatedBalanceText = document.getElementById('calculatedBalanceText');

            function recalculate() {
                const price = parseFloat(priceInput.value) || 0;
                const dType = discountTypeSelect.value;
                const dVal = parseFloat(discountInput.value) || 0;

                let discount = 0;
                if (dType === 'percentage') {
                    discount = Math.min(price, Math.round(price * (dVal / 100) * 100) / 100);
                    discountPercentHidden.value = dVal;
                    discountHidden.value = discount;
                } else {
                    discount = Math.min(price, dVal);
                    discountPercentHidden.value = '';
                    discountHidden.value = discount;
                }

                const netTotal = Math.max(0, price - discount);
                netTotalDisplay.value = netTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

                const paid = paidAmountInput.value !== '' ? (parseFloat(paidAmountInput.value) || 0) : netTotal;
                const balanceDue = Math.max(0, netTotal - paid);

                calculatedBalanceText.innerText = 'Balance Due: PKR ' + balanceDue.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                if (balanceDue > 0) {
                    calculatedBalanceText.className = 'fw-bold text-danger';
                } else {
                    calculatedBalanceText.className = 'fw-bold text-success';
                }
            }

            treatmentSelect.addEventListener('change', function () {
                const opt = this.options[this.selectedIndex];
                const price = opt ? opt.getAttribute('data-price') : null;
                if (price) {
                    priceInput.value = price;
                    recalculate();
                }
            });

            priceInput.addEventListener('input', recalculate);
            discountTypeSelect.addEventListener('change', recalculate);
            discountInput.addEventListener('input', recalculate);
            paidAmountInput.addEventListener('input', recalculate);

            // Initial calculation run
            recalculate();
        });
    </script>
@endsection
