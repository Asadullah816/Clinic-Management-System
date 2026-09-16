@extends('layouts.app')

@section('title', 'Stock Transaction')

@section('content')

    <div class="card shadow-sm" style="max-width: 820px;">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">New Stock Transaction</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('stock.store') }}">
                @csrf

                @if (request('redirect') === 'medicine')
                    <input type="hidden" name="redirect" value="medicine">
                @endif

                <div class="mb-3">
                    <label class="form-label d-block">Type <span class="text-danger">*</span></label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="type" id="type-in" value="in"
                            {{ old('type', $type) === 'in' ? 'checked' : '' }}>
                        <label class="form-check-label text-success fw-semibold" for="type-in">
                            Stock In <span class="text-muted fw-normal">(purchase / restock)</span>
                        </label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="type" id="type-out" value="out"
                            {{ old('type', $type) === 'out' ? 'checked' : '' }}>
                        <label class="form-check-label text-danger fw-semibold" for="type-out">
                            Stock Out <span class="text-muted fw-normal">(usage / adjustment)</span>
                        </label>
                    </div>
                    @error('type')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="medicine_id" class="form-label">Product <span class="text-danger">*</span></label>
                        <select name="medicine_id" id="medicine_id"
                            class="form-select @error('medicine_id') is-invalid @enderror" required>
                            <option value="">-- Select product --</option>
                            @foreach ($medicines as $m)
                                <option value="{{ $m->id }}" data-stock="{{ $m->stock_quantity }}"
                                    {{ (string) old('medicine_id', $medicineId) === (string) $m->id ? 'selected' : '' }}>
                                    {{ $m->name }} (Stock: {{ $m->stock_quantity }} {{ $m->unit }})
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
                            value="{{ old('quantity') }}" class="form-control @error('quantity') is-invalid @enderror"
                            required>
                        @error('quantity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="transaction_date" class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="date" id="transaction_date" name="transaction_date"
                            value="{{ old('transaction_date', now()->toDateString()) }}"
                            class="form-control @error('transaction_date') is-invalid @enderror" required>
                        @error('transaction_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div id="stock-in-fields" class="row">
                    <div class="col-md-6 mb-3">
                        <label for="supplier_id" class="form-label">Supplier</label>
                        <select name="supplier_id" id="supplier_id"
                            class="form-select @error('supplier_id') is-invalid @enderror">
                            <option value="">-- No supplier --</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}"
                                    {{ (string) old('supplier_id') === (string) $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('supplier_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="unit_cost" class="form-label">Unit Cost</label>
                        <input type="number" step="0.01" min="0" id="unit_cost" name="unit_cost"
                            value="{{ old('unit_cost') }}" class="form-control @error('unit_cost') is-invalid @enderror">
                        <div class="form-text">Cost per unit for this purchase.</div>
                        @error('unit_cost')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="record_expense" value="1"
                                id="record_expense" {{ old('record_expense') ? 'checked' : '' }}>
                            <label class="form-check-label" for="record_expense">
                                Also record this purchase as an <strong>expense</strong>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3 expense-block" style="display: none;">
                        <label for="expense_category_id" class="form-label">Expense Category <span
                                class="text-danger">*</span></label>
                        <select name="expense_category_id" id="expense_category_id"
                            class="form-select @error('expense_category_id') is-invalid @enderror">
                            <option value="">-- Select category --</option>
                            @foreach ($expenseCategories as $category)
                                <option value="{{ $category->id }}"
                                    {{ (string) old('expense_category_id', $defaultExpenseCategoryId) === (string) $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">Defaults to "Medicine Purchase" when available.</div>
                        @error('expense_category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3 expense-block" style="display: none;">
                        <label for="expense_payment_method" class="form-label">Paid By</label>
                        <select name="expense_payment_method" id="expense_payment_method"
                            class="form-select @error('expense_payment_method') is-invalid @enderror">
                            @foreach (\App\Models\Expense::methods() as $methodValue => $methodLabel)
                                <option value="{{ $methodValue }}"
                                    {{ old('expense_payment_method', 'cash') === $methodValue ? 'selected' : '' }}>
                                    {{ $methodLabel }}
                                </option>
                            @endforeach
                        </select>
                        @error('expense_payment_method')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="alert alert-info py-2 small">
                    <i class="bi bi-info-circle me-1"></i>
                    A Stock In with a supplier and unit cost acts as the <strong>purchase record</strong>.
                    The option to also post it as an <strong>expense</strong> arrives in Phase 12.
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="reference" class="form-label">Reference</label>
                        <input type="text" id="reference" name="reference" value="{{ old('reference') }}"
                            class="form-control @error('reference') is-invalid @enderror"
                            placeholder="e.g. invoice / delivery note #">
                        @error('reference')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <input type="text" id="notes" name="notes" value="{{ old('notes') }}"
                            class="form-control @error('notes') is-invalid @enderror">
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Save
                        Transaction</button>
                    <a href="{{ route('stock.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    {{-- <script>
        var medicineSelect = document.getElementById('medicine_id');
        var quantityInput = document.getElementById('quantity');
        var stockHint = document.getElementById('stock-hint');
        var inFields = document.getElementById('stock-in-fields');

        function currentType() {
            return document.querySelector('input[name="type"]:checked').value;
        }

        function updateForm() {
            var type = currentType();

            // Supplier + unit cost only apply to Stock In
            inFields.classList.toggle('d-none', type !== 'in');

            var option = medicineSelect.options[medicineSelect.selectedIndex];
            if (option && option.value) {
                var stock = parseInt(option.dataset.stock, 10);
                if (type === 'out') {
                    quantityInput.setAttribute('max', stock);
                    stockHint.textContent = 'Current stock: ' + stock + '. Maximum removable: ' + stock + '.';
                } else {
                    quantityInput.removeAttribute('max');
                    stockHint.textContent = 'Current stock: ' + stock + '.';
                }
            } else {
                quantityInput.removeAttribute('max');
                stockHint.textContent = '';
            }
        }

        document.querySelectorAll('input[name="type"]').forEach(function(radio) {
            radio.addEventListener('change', updateForm);
        });
        medicineSelect.addEventListener('change', updateForm);
        updateForm();
    </script> --}}
    <script>
        var medicineSelect = document.getElementById('medicine_id');
        var quantityInput = document.getElementById('quantity');
        var stockHint = document.getElementById('stock-hint');
        var inFields = document.getElementById('stock-in-fields');
        var recordExpense = document.getElementById('record_expense');
        var expenseBlocks = document.querySelectorAll('.expense-block');

        function currentType() {
            return document.querySelector('input[name="type"]:checked').value;
        }

        function updateForm() {
            var type = currentType();

            // Supplier + unit cost + expense option only apply to Stock In
            inFields.classList.toggle('d-none', type !== 'in');

            var option = medicineSelect.options[medicineSelect.selectedIndex];
            if (option && option.value) {
                var stock = parseInt(option.dataset.stock, 10);
                if (type === 'out') {
                    quantityInput.setAttribute('max', stock);
                    stockHint.textContent = 'Current stock: ' + stock + '. Maximum removable: ' + stock + '.';
                } else {
                    quantityInput.removeAttribute('max');
                    stockHint.textContent = 'Current stock: ' + stock + '.';
                }
            } else {
                quantityInput.removeAttribute('max');
                stockHint.textContent = '';
            }

            updateExpenseBlocks();
        }

        function updateExpenseBlocks() {
            var show = recordExpense.checked && currentType() === 'in';
            expenseBlocks.forEach(function(block) {
                block.style.display = show ? '' : 'none';
            });
        }

        document.querySelectorAll('input[name="type"]').forEach(function(radio) {
            radio.addEventListener('change', updateForm);
        });
        medicineSelect.addEventListener('change', updateForm);
        recordExpense.addEventListener('change', updateExpenseBlocks);
        updateForm();
    </script>

@endsection
