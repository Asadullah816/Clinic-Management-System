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
        <label for="purchase_price" class="form-label">Purchase Price (PKR) <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text bg-white">PKR</span>
            <input type="number" step="0.01" min="0" id="purchase_price" name="purchase_price"
                value="{{ old('purchase_price', $medicine->purchase_price ?? '') }}"
                class="form-control @error('purchase_price') is-invalid @enderror" placeholder="0.00" required>
        </div>
        @error('purchase_price')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-3 mb-3">
        <label for="selling_price" class="form-label">Selling Price (PKR) <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text bg-white">PKR</span>
            <input type="number" step="0.01" min="0" id="selling_price" name="selling_price"
                value="{{ old('selling_price', $medicine->selling_price ?? '') }}"
                class="form-control @error('selling_price') is-invalid @enderror" placeholder="0.00" required>
        </div>
        <div class="form-text">Set 0 for supplies used internally.</div>
        @error('selling_price')
            <div class="invalid-feedback d-block">{{ $message }}</div>
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
<div class="row">
    <div class="col-md-6 mb-3">
        <label for="image" class="form-label">Product Image</label>
        <input type="file" id="image" name="image" class="form-control @error('image') is-invalid @enderror"
            accept="image/*" onchange="previewMedicineImage(this)">
        <div class="form-text">Upload product picture (JPG, PNG, WebP up to 2MB).</div>
        @error('image')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror

        @if (!empty($medicine?->image_url))
            <div class="mt-2 p-2 border rounded bg-light d-flex align-items-center gap-3">
                <img src="{{ $medicine->image_url }}" alt="{{ $medicine->name }}" class="rounded border" style="width: 50px; height: 50px; object-fit: cover;">
                <div class="form-check mb-0">
                    <input class="form-check-input" type="checkbox" name="remove_image" value="1" id="remove_image">
                    <label class="form-check-label text-danger small" for="remove_image">
                        Remove current image
                    </label>
                </div>
            </div>
        @endif
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label d-block">Image Preview</label>
        <div id="image-preview-container" class="border rounded bg-light d-flex align-items-center justify-content-center"
            style="width: 90px; height: 90px; overflow: hidden;">
            @if (!empty($medicine?->image_url))
                <img id="image-preview" src="{{ $medicine->image_url }}" alt="Preview" style="width: 100%; height: 100%; object-fit: cover;">
            @else
                <i id="image-placeholder" class="bi bi-image text-muted fs-2"></i>
                <img id="image-preview" src="#" alt="Preview" class="d-none" style="width: 100%; height: 100%; object-fit: cover;">
            @endif
        </div>
    </div>
</div>

<script>
    function previewMedicineImage(input) {
        var preview = document.getElementById('image-preview');
        var placeholder = document.getElementById('image-placeholder');
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.classList.remove('d-none');
                if (placeholder) placeholder.classList.add('d-none');
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>

<div class="alert alert-light border py-2 small text-muted mb-0">
    <i class="bi bi-info-circle me-1"></i>
    The stock quantity here is for initial setup / corrections. Day-to-day changes should be made
    through the <strong>Stock</strong> module (arrives in Phase 10) so every change leaves a record.
</div>
