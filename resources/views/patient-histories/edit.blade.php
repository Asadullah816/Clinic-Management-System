@extends('layouts.app')

@section('title', 'Edit Historical Record: ' . $history->record_number)

@section('content')

    <div class="row justify-content-center">
        <div class="col-lg-10">

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="bi bi-pencil-square text-primary me-2"></i>Edit Historical Record: {{ $history->record_number }}
                        </h5>
                        <div class="text-muted small">
                            Patient: <strong>{{ $history->patient->full_name }}</strong> (#{{ $history->patient->patient_number }})
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('patient-histories.show', $history) }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left me-1"></i> Back to Record
                        </a>
                    </div>
                </div>

                <div class="card-body p-4">
                    <form method="POST" action="{{ route('patient-histories.update', $history) }}">
                        @csrf
                        @method('PUT')

                        {{-- Attendance Details --}}
                        <div class="card mb-4 border shadow-sm">
                            <div class="card-header bg-light py-2">
                                <span class="fw-semibold text-dark">
                                    <i class="bi bi-bandaid text-primary me-1"></i> Pre-App Attendance Details
                                </span>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="visit_date" class="form-label fw-semibold">Past Visit / Attendance Date <span class="text-danger">*</span></label>
                                        <input type="date" id="visit_date" name="visit_date"
                                            value="{{ old('visit_date', $history->visit_date->format('Y-m-d')) }}"
                                            max="{{ today()->toDateString() }}"
                                            class="form-control @error('visit_date') is-invalid @enderror" required>
                                        @error('visit_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="doctor_id" class="form-label fw-semibold">Attending Doctor / Dentist</label>
                                        @if (auth()->user()->isDoctor())
                                            <input type="hidden" name="doctor_id" value="{{ auth()->id() }}">
                                            <input type="text" class="form-control bg-light fw-semibold" value="{{ auth()->user()->name }} (You)" readonly>
                                        @else
                                            <select name="doctor_id" id="doctor_id" class="form-select @error('doctor_id') is-invalid @enderror">
                                                <option value="">-- Select from Registered Doctors --</option>
                                                @foreach ($doctors as $doc)
                                                    <option value="{{ $doc->id }}"
                                                        {{ (string) old('doctor_id', $history->doctor_id) === (string) $doc->id ? 'selected' : '' }}>
                                                        {{ $doc->name }} ({{ $doc->role === 'admin' ? 'Super Admin / Lead' : 'Doctor' }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="mt-2">
                                                <input type="text" name="doctor_name" value="{{ old('doctor_name', $history->doctor_name) }}"
                                                    class="form-control form-control-sm" placeholder="Or custom doctor name...">
                                            </div>
                                        @endif
                                        @error('doctor_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="procedures" class="form-label fw-semibold">
                                        Procedures / Treatments Performed <span class="text-danger">*</span>
                                    </label>
                                    <textarea id="procedures" name="procedures" rows="3"
                                        class="form-control @error('procedures') is-invalid @enderror" required>{{ old('procedures', $history->procedures) }}</textarea>
                                    @error('procedures') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="tooth_numbers" class="form-label fw-semibold">Tooth Numbers / Region</label>
                                        <input type="text" id="tooth_numbers" name="tooth_numbers"
                                            value="{{ old('tooth_numbers', $history->tooth_numbers) }}"
                                            class="form-control @error('tooth_numbers') is-invalid @enderror">
                                        @error('tooth_numbers') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-8 mb-3">
                                        <label for="diagnosis" class="form-label fw-semibold">Previous Diagnosis / Findings</label>
                                        <input type="text" id="diagnosis" name="diagnosis"
                                            value="{{ old('diagnosis', $history->diagnosis) }}"
                                            class="form-control @error('diagnosis') is-invalid @enderror">
                                        @error('diagnosis') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <div class="mb-0">
                                    <label for="notes" class="form-label fw-semibold">Clinical &amp; General History Notes</label>
                                    <textarea id="notes" name="notes" rows="2"
                                        class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $history->notes) }}</textarea>
                                    @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Financials --}}
                        <div class="card mb-4 border shadow-sm">
                            <div class="card-header bg-light py-2 fw-semibold text-dark">
                                <i class="bi bi-cash-stack text-success me-1"></i> Treatment Financial Details
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label for="total_cost" class="form-label fw-semibold">Total Cost (PKR) <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" min="0" id="total_cost" name="total_cost"
                                            value="{{ old('total_cost', $history->total_cost) }}"
                                            class="form-control fw-bold @error('total_cost') is-invalid @enderror" required>
                                        @error('total_cost') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Paid Amount</label>
                                        <input type="text" class="form-control bg-light text-success fw-bold"
                                            value="PKR {{ number_format($history->paid_amount, 2) }}" readonly>
                                        <div class="form-text small">Payments are logged in payments ledger.</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Calculated Due</label>
                                        <input type="text" id="calculated_due_display" class="form-control bg-light text-danger fw-bold"
                                            value="PKR {{ number_format($history->due_amount, 2) }}" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <form method="POST" action="{{ route('patient-histories.destroy', $history) }}"
                                onsubmit="return confirm('Delete this historical record?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger">
                                    <i class="bi bi-trash me-1"></i> Delete Record
                                </button>
                            </form>

                            <div class="d-flex gap-2">
                                <a href="{{ route('patient-histories.show', $history) }}" class="btn btn-outline-secondary">
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="bi bi-check-lg me-1"></i> Update Record
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

<script>
    const totalCostInput = document.getElementById('total_cost');
    const paidAmount = {{ (float) $history->paid_amount }};
    const calculatedDueDisplay = document.getElementById('calculated_due_display');

    totalCostInput.addEventListener('input', function() {
        const cost = parseFloat(this.value) || 0;
        const due = Math.max(0, cost - paidAmount);
        calculatedDueDisplay.value = 'PKR ' + due.toFixed(2);
    });
</script>

@endsection
