@extends('layouts.app')

@section('title', 'Add Laser Expense')

@section('content')
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card border shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 fw-bold" style="color: #0f172a;">Record Laser Expense</h5>
                        <p class="text-muted small mb-0">Track consumable, maintenance, and operating costs separate from clinic expenses.</p>
                    </div>
                    <a href="{{ route('laser.expenses.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Back to Expenses
                    </a>
                </div>

                <div class="card-body p-4">
                    <form method="POST" action="{{ route('laser.expenses.store') }}">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="laser_expense_category_id" class="form-label fw-semibold">Expense Category <span class="text-danger">*</span></label>
                                <select name="laser_expense_category_id" id="laser_expense_category_id" class="form-select @error('laser_expense_category_id') is-invalid @enderror" required>
                                    <option value="">-- Choose Category --</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ old('laser_expense_category_id') == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('laser_expense_category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="expense_date" class="form-label fw-semibold">Expense Date <span class="text-danger">*</span></label>
                                <input type="date" name="expense_date" id="expense_date"
                                       class="form-control @error('expense_date') is-invalid @enderror"
                                       value="{{ old('expense_date', date('Y-m-d')) }}" required>
                                @error('expense_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="title" class="form-label fw-semibold">Title / Description <span class="text-danger">*</span></label>
                                <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror"
                                       value="{{ old('title') }}" placeholder="e.g. Diode handpiece servicing, Cooling gel refill, Carbon cream" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="amount" class="form-label fw-semibold">Amount (PKR) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white">PKR</span>
                                    <input type="number" step="0.01" min="0.01" name="amount" id="amount"
                                           class="form-control fw-bold text-danger @error('amount') is-invalid @enderror"
                                           value="{{ old('amount') }}" placeholder="0.00" required>
                                </div>
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="payment_method" class="form-label fw-semibold">Paid Method <span class="text-danger">*</span></label>
                                <select name="payment_method" id="payment_method" class="form-select @error('payment_method') is-invalid @enderror" required>
                                    @foreach ($methods as $key => $label)
                                        <option value="{{ $key }}" {{ old('payment_method', 'cash') === $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('payment_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="reference" class="form-label fw-semibold">Receipt / Reference #</label>
                                <input type="text" name="reference" id="reference" class="form-control @error('reference') is-invalid @enderror"
                                       value="{{ old('reference') }}" placeholder="e.g. Voucher or bill #">
                                @error('reference')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="description" class="form-label fw-semibold">Additional Details</label>
                                <textarea name="description" id="description" rows="3"
                                          class="form-control @error('description') is-invalid @enderror"
                                          placeholder="Supplier details, invoice specifics...">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-4 border-top mt-4">
                            <a href="{{ route('laser.expenses.index') }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-danger px-4 fw-semibold">
                                <i class="bi bi-check-lg me-1"></i> Save Expense
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
