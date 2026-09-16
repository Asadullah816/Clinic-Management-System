{{-- Expects: $expense (null on create), $categories --}}

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="expense_category_id" class="form-label">Category <span class="text-danger">*</span></label>
        <select name="expense_category_id" id="expense_category_id"
            class="form-select @error('expense_category_id') is-invalid @enderror" required>
            <option value="">-- Select category --</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}"
                    {{ (string) old('expense_category_id', $expense->expense_category_id ?? '') === (string) $category->id ? 'selected' : '' }}>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
        @error('expense_category_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" id="title" name="title" value="{{ old('title', $expense->title ?? '') }}"
            class="form-control @error('title') is-invalid @enderror"
            placeholder="e.g. Monthly Rent, Staff Salary — June" required>
        @error('title')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
        <input type="number" step="0.01" min="0.01" id="amount" name="amount"
            value="{{ old('amount', $expense->amount ?? '') }}"
            class="form-control @error('amount') is-invalid @enderror" required>
        @error('amount')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4 mb-3">
        <label for="expense_date" class="form-label">Expense Date <span class="text-danger">*</span></label>
        <input type="date" id="expense_date" name="expense_date"
            value="{{ old('expense_date', $expense?->expense_date?->format('Y-m-d') ?? now()->toDateString()) }}"
            class="form-control @error('expense_date') is-invalid @enderror" required>
        @error('expense_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4 mb-3">
        <label for="payment_method" class="form-label">Payment Method <span class="text-danger">*</span></label>
        <select name="payment_method" id="payment_method"
            class="form-select @error('payment_method') is-invalid @enderror" required>
            @foreach (\App\Models\Expense::methods() as $methodValue => $methodLabel)
                <option value="{{ $methodValue }}"
                    {{ old('payment_method', $expense->payment_method ?? 'cash') === $methodValue ? 'selected' : '' }}>
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
    <input type="text" id="reference" name="reference" value="{{ old('reference', $expense->reference ?? '') }}"
        class="form-control @error('reference') is-invalid @enderror" placeholder="e.g. bill number, cheque #">
    @error('reference')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="description" class="form-label">Description</label>
    <textarea id="description" name="description" rows="2"
        class="form-control @error('description') is-invalid @enderror">{{ old('description', $expense->description ?? '') }}</textarea>
    @error('description')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
