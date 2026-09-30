@extends('layouts.app')

@section('title', 'Edit Laser Session ' . $session->invoice_number)

@section('content')
    <div class="row justify-content-center">
        <div class="col-12 col-xl-10">
            <div class="card border shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 fw-bold" style="color: #0f172a;">Edit Laser Session {{ $session->invoice_number }}</h5>
                        <p class="text-muted small mb-0">{{ $session->patient->full_name }} &bull; {{ $session->session_date->format('d M Y') }}</p>
                    </div>
                    <a href="{{ route('laser.sessions.show', $session) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Back to Details
                    </a>
                </div>

                <div class="card-body p-4">
                    <form method="POST" action="{{ route('laser.sessions.update', $session) }}">
                        @csrf
                        @method('PUT')

                        {{-- Section 1: Patient & Procedure --}}
                        <h6 class="fw-bold text-primary mb-3"><i class="bi bi-person-badge me-1"></i> 1. Patient &amp; Procedure</h6>
                        <div class="row g-3 mb-4 p-3 bg-light rounded-3 border">
                            <div class="col-md-6">
                                <label for="laser_patient_id" class="form-label fw-semibold">Patient <span class="text-danger">*</span></label>
                                <select name="laser_patient_id" id="laser_patient_id" class="form-select @error('laser_patient_id') is-invalid @enderror" required>
                                    @foreach ($patients as $pat)
                                        <option value="{{ $pat->id }}" {{ old('laser_patient_id', $session->laser_patient_id) == $pat->id ? 'selected' : '' }}>
                                            {{ $pat->full_name }} ({{ $pat->patient_number }})
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
                                    @foreach ($treatments as $treatment)
                                        <option value="{{ $treatment->id }}" {{ old('laser_treatment_id', $session->laser_treatment_id) == $treatment->id ? 'selected' : '' }}>
                                            {{ $treatment->name }}
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
                                       value="{{ old('session_date', $session->session_date->format('Y-m-d')) }}" required>
                                @error('session_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="session_number" class="form-label fw-semibold">Session Number <span class="text-danger">*</span></label>
                                <input type="number" min="1" name="session_number" id="session_number"
                                       class="form-control @error('session_number') is-invalid @enderror"
                                       value="{{ old('session_number', $session->session_number) }}" required>
                                @error('session_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="total_sessions" class="form-label fw-semibold">Total Planned Sessions</label>
                                <input type="number" min="1" name="total_sessions" id="total_sessions"
                                       class="form-control @error('total_sessions') is-invalid @enderror"
                                       value="{{ old('total_sessions', $session->total_sessions) }}">
                                @error('total_sessions')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Section 2: Machine Settings & Clinical Parameters --}}
                        <h6 class="fw-bold text-primary mb-3"><i class="bi bi-sliders me-1"></i> 2. Machine &amp; Technical Parameters</h6>
                        <div class="row g-3 mb-4 p-3 bg-light rounded-3 border">
                            <div class="col-md-4">
                                <label for="laser_machine" class="form-label fw-semibold">Laser Machine</label>
                                <input type="text" name="laser_machine" id="laser_machine"
                                       class="form-control @error('laser_machine') is-invalid @enderror"
                                       value="{{ old('laser_machine', $session->laser_machine) }}">
                                @error('laser_machine')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-2">
                                <label for="fluence" class="form-label fw-semibold">Fluence (J/cm²)</label>
                                <input type="text" name="fluence" id="fluence"
                                       class="form-control @error('fluence') is-invalid @enderror"
                                       value="{{ old('fluence', $session->fluence) }}">
                                @error('fluence')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-2">
                                <label for="pulse_width" class="form-label fw-semibold">Pulse Width (ms)</label>
                                <input type="text" name="pulse_width" id="pulse_width"
                                       class="form-control @error('pulse_width') is-invalid @enderror"
                                       value="{{ old('pulse_width', $session->pulse_width) }}">
                                @error('pulse_width')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-2">
                                <label for="spot_size" class="form-label fw-semibold">Spot Size</label>
                                <input type="text" name="spot_size" id="spot_size"
                                       class="form-control @error('spot_size') is-invalid @enderror"
                                       value="{{ old('spot_size', $session->spot_size) }}">
                                @error('spot_size')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-2">
                                <label for="pulses_count" class="form-label fw-semibold">Shots Fired</label>
                                <input type="number" min="0" name="pulses_count" id="pulses_count"
                                       class="form-control @error('pulses_count') is-invalid @enderror"
                                       value="{{ old('pulses_count', $session->pulses_count) }}">
                                @error('pulses_count')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="performed_by" class="form-label fw-semibold">Operator / Specialist</label>
                                <select name="performed_by" id="performed_by" class="form-select @error('performed_by') is-invalid @enderror">
                                    <option value="">Select Doctor...</option>
                                    @foreach ($doctors as $doc)
                                        <option value="{{ $doc->id }}" {{ old('performed_by', $session->performed_by) == $doc->id ? 'selected' : '' }}>
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
                                       value="{{ old('notes', $session->notes) }}">
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Section 3: Billing & Separate Discount --}}
                        <h6 class="fw-bold text-primary mb-3"><i class="bi bi-cash-coin me-1"></i> 3. Billing &amp; Separate Laser Discount</h6>
                        <div class="row g-3 mb-4 p-3 bg-light rounded-3 border">
                            <div class="col-md-4">
                                <label for="price" class="form-label fw-semibold">Standard Price (PKR) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white">PKR</span>
                                    <input type="number" step="0.01" min="0" name="price" id="price"
                                           class="form-control fw-bold @error('price') is-invalid @enderror"
                                           value="{{ old('price', $session->price) }}" required>
                                </div>
                                @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Laser Discount</label>
                                <div class="input-group">
                                    <select name="discount_type" id="discount_type" class="form-select" style="max-width: 90px;">
                                        <option value="fixed" {{ old('discount_type', $session->discount_type) === 'percentage' ? '' : 'selected' }}>PKR</option>
                                        <option value="percentage" {{ old('discount_type', $session->discount_type) === 'percentage' ? 'selected' : '' }}>%</option>
                                    </select>
                                    <input type="number" step="0.01" min="0" name="discount_input" id="discount_input"
                                           class="form-control"
                                           value="{{ old('discount_percentage', $session->discount_percentage) ?: old('discount', $session->discount) }}">
                                </div>
                                <input type="hidden" name="discount" id="discount_hidden" value="{{ old('discount', $session->discount) }}">
                                <input type="hidden" name="discount_percentage" id="discount_percentage_hidden" value="{{ old('discount_percentage', $session->discount_percentage) }}">
                                @error('discount')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Net Total Payable</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-primary text-white fw-bold">PKR</span>
                                    <input type="text" id="net_total_display" class="form-control fw-bold fs-5 bg-white text-primary" readonly value="{{ number_format($session->total_amount, 2) }}">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label for="paid_amount" class="form-label fw-semibold">Amount Paid (PKR)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-success-subtle text-success fw-bold">PKR</span>
                                    <input type="number" step="0.01" min="0" name="paid_amount" id="paid_amount"
                                           class="form-control fw-bold text-success @error('paid_amount') is-invalid @enderror"
                                           value="{{ old('paid_amount', $session->paid_amount) }}">
                                </div>
                                @error('paid_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="payment_method" class="form-label fw-semibold">Payment Method</label>
                                <select name="payment_method" id="payment_method" class="form-select @error('payment_method') is-invalid @enderror">
                                    <option value="cash" {{ old('payment_method', $session->payment_method) === 'cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="card" {{ old('payment_method', $session->payment_method) === 'card' ? 'selected' : '' }}>Credit / Debit Card</option>
                                    <option value="bank" {{ old('payment_method', $session->payment_method) === 'bank' ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="online" {{ old('payment_method', $session->payment_method) === 'online' ? 'selected' : '' }}>Online / JazzCash / EasyPaisa</option>
                                    <option value="other" {{ old('payment_method', $session->payment_method) === 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('payment_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="payment_reference" class="form-label fw-semibold">Reference / Txn #</label>
                                <input type="text" name="payment_reference" id="payment_reference"
                                       class="form-control @error('payment_reference') is-invalid @enderror"
                                       value="{{ old('payment_reference', $session->payment_reference) }}">
                                @error('payment_reference')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                            <a href="{{ route('laser.sessions.show', $session) }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4 fw-semibold">
                                <i class="bi bi-check-lg me-1"></i> Update Session
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const priceInput = document.getElementById('price');
            const discountTypeSelect = document.getElementById('discount_type');
            const discountInput = document.getElementById('discount_input');
            const discountHidden = document.getElementById('discount_hidden');
            const discountPercentHidden = document.getElementById('discount_percentage_hidden');
            const netTotalDisplay = document.getElementById('net_total_display');

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
            }

            priceInput.addEventListener('input', recalculate);
            discountTypeSelect.addEventListener('change', recalculate);
            discountInput.addEventListener('input', recalculate);
        });
    </script>
@endsection
