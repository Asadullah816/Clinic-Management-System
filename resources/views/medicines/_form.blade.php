{{-- Expects: $medicine (null on create), $categories, $suppliers --}}

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
        <input type="text" id="name" name="name" value="{{ old('name', $medicine->name ?? '') }}"
            class="form-control @error('name') is-invalid @enderror" placeholder="e.g. Vitamin C Serum" required>
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="generic_name" class="form-label">Generic Name</label>
        <input type="text" id="generic_name" name="generic_name"
            value="{{ old('generic_name', $medicine->generic_name ?? '') }}"
            class="form-control @error('generic_name') is-invalid @enderror" placeholder="e.g. Ascorbic Acid 10%">
        @error('generic_name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label for="medicine_category_id" class="form-label">Category <span class="text-danger">*</span></label>
        <select name="medicine_category_id" id="medicine_category_id"
            class="form-select @error('medicine_category_id') is-invalid @enderror" required>
            <option value="">-- Select category --</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}"
                    {{ (string) old('medicine_category_id', $medicine->medicine_category_id ?? '') === (string) $category->id ? 'selected' : '' }}>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
        @error('medicine_category_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4 mb-3">
        <label for="supplier_id" class="form-label">Supplier</label>
        <select name="supplier_id" id="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror">
            <option value="">-- No supplier --</option>
            @foreach ($suppliers as $supplier)
                <option value="{{ $supplier->id }}"
                    {{ (string) old('supplier_id', $medicine->supplier_id ?? '') === (string) $supplier->id ? 'selected' : '' }}>
                    {{ $supplier->name }}{{ $supplier->company ? ' (' . $supplier->company . ')' : '' }}
                </option>
            @endforeach
        </select>
        @error('supplier_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4 mb-3">
        <label for="unit" class="form-label">Unit</label>
        <select name="unit" id="unit" class="form-select @error('unit') is-invalid @enderror">
            <option value="">-- Select unit --</option>
            @foreach (\App\Models\Medicine::units() as $unit)
                <option value="{{ $unit }}"
                    {{ old('unit', $medicine->unit ?? '') === $unit ? 'selected' : '' }}>
                    {{ $unit }}
                </option>
            @endforeach
        </select>
        @error('unit')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row">
    <div class="col-md-3 mb-3">
        <label for="purchase_price" class="form-label">Purchase Price <span class="text-danger">*</span></label>
        <input type="number" step="0.01" min="0" id="purchase_price" name="purchase_price"
            value="{{ old('purchase_price', $medicine->purchase_price ?? '') }}"
            class="form-control @error('purchase_price') is-invalid @enderror" required>
        @error('purchase_price')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-3 mb-3">
        <label for="selling_price" class="form-label">Selling Price <span class="text-danger">*</span></label>
        <input type="number" step="0.01" min="0" id="selling_price" name="selling_price"
            value="{{ old('selling_price', $medicine->selling_price ?? '') }}"
            class="form-control @error('selling_price') is-invalid @enderror" required>
        <div class="form-text">Set 0 for supplies used internally.</div>
        @error('selling_price')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-3 mb-3">
        <label for="stock_quantity" class="form-label">Stock Quantity <span class="text-danger">*</span></label>
        <input type="number" step="1" min="0" id="stock_quantity" name="stock_quantity"
            value="{{ old('stock_quantity', $medicine->stock_quantity ?? 0) }}"
            class="form-control @error('stock_quantity') is-invalid @enderror" required>
        @error('stock_quantity')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-3 mb-3">
        <label for="minimum_stock" class="form-label">Minimum Stock <span class="text-danger">*</span></label>
        <input type="number" step="1" min="0" id="minimum_stock" name="minimum_stock"
            value="{{ old('minimum_stock', $medicine->minimum_stock ?? 0) }}"
            class="form-control @error('minimum_stock') is-invalid @enderror" required>
        <div class="form-text">Low-stock alert threshold.</div>
        @error('minimum_stock')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label for="expiry_date" class="form-label">Expiry Date</label>
        <input type="date" id="expiry_date" name="expiry_date"
            value="{{ old('expiry_date', $medicine?->expiry_date?->format('Y-m-d')) }}"
            class="form-control @error('expiry_date') is-invalid @enderror">
        @error('expiry_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4 mb-3">
        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
        <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
            <option value="active" {{ old('status', $medicine->status ?? 'active') === 'active' ? 'selected' : '' }}>
                Active</option>
            <option value="inactive"
                {{ old('status', $medicine->status ?? 'active') === 'inactive' ? 'selected' : '' }}>Inactive</option>
        </select>
        @error('status')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4 mb-3">
        <label for="description" class="form-label">Description</label>
        <textarea id="description" name="description" rows="1"
            class="form-control @error('description') is-invalid @enderror">{{ old('description', $medicine->description ?? '') }}</textarea>
        @error('description')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="alert alert-light border py-2 small text-muted mb-0">
    <i class="bi bi-info-circle me-1"></i>
    The stock quantity here is for initial setup / corrections. Day-to-day changes should be made
    through the <strong>Stock</strong> module (arrives in Phase 10) so every change leaves a record.
</div>
