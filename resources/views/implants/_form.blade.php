<div class="row g-3">
    {{-- Basic Identifiers --}}
    <div class="col-md-4">
        <label for="item_code" class="form-label fw-semibold">Item / Product Code</label>
        <input type="text" id="item_code" name="item_code"
            value="{{ old('item_code', $implant->item_code ?? '') }}"
            class="form-control @error('item_code') is-invalid @enderror"
            placeholder="e.g. IMP-0001 (auto-generated if left empty)">
        <div class="form-text small">Leave blank to generate automatically.</div>
        @error('item_code')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="name" class="form-label fw-semibold">Implant Model / Product Name <span class="text-danger">*</span></label>
        <input type="text" id="name" name="name"
            value="{{ old('name', $implant->name ?? '') }}"
            class="form-control @error('name') is-invalid @enderror" required
            placeholder="e.g. BLX Bone Level Tapered, TSIII SA Fixture">
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="brand" class="form-label fw-semibold">Brand / Manufacturer (Optional)</label>
        <input type="text" id="brand" name="brand" list="brandList"
            value="{{ old('brand', $implant->brand ?? '') }}"
            class="form-control @error('brand') is-invalid @enderror"
            placeholder="e.g. Straumann, Osstem, Dentium, Megagen">
        <datalist id="brandList">
            <option value="Straumann">
            <option value="Osstem">
            <option value="Dentium">
            <option value="Megagen">
            <option value="Nobel Biocare">
            <option value="Hiossen">
            <option value="Zimmer Biomet">
            <option value="Neodent">
            <option value="BioHorizons">
            <option value="MIS Implants">
        </datalist>
        <div class="form-text small">Select or type any dental implant brand.</div>
        @error('brand')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Implant Dimensions & Technical Details --}}
    <div class="col-12 mt-4">
        <h6 class="fw-bold text-dark border-bottom pb-2">
            <i class="bi bi-rulers text-primary me-1"></i> Dimensions &amp; Technical Specifications
        </h6>
    </div>

    <div class="col-md-3">
        <label for="diameter" class="form-label fw-semibold">Diameter (Ø)</label>
        <div class="input-group">
            <input type="text" id="diameter" name="diameter"
                value="{{ old('diameter', $implant->diameter ?? '') }}"
                class="form-control @error('diameter') is-invalid @enderror"
                placeholder="e.g. 3.5, 4.0, 4.5, 5.0">
            <span class="input-group-text bg-light text-muted">mm</span>
        </div>
        @error('diameter')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label for="length" class="form-label fw-semibold">Length</label>
        <div class="input-group">
            <input type="text" id="length" name="length"
                value="{{ old('length', $implant->length ?? '') }}"
                class="form-control @error('length') is-invalid @enderror"
                placeholder="e.g. 8.0, 10.0, 11.5, 13.0">
            <span class="input-group-text bg-light text-muted">mm</span>
        </div>
        @error('length')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label for="platform" class="form-label fw-semibold">Platform / Connection</label>
        <input type="text" id="platform" name="platform"
            value="{{ old('platform', $implant->platform ?? '') }}"
            class="form-control @error('platform') is-invalid @enderror"
            placeholder="e.g. Regular CrossFit, Conical Hex">
        @error('platform')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label for="material" class="form-label fw-semibold">Material / Surface</label>
        <input type="text" id="material" name="material"
            value="{{ old('material', $implant->material ?? '') }}"
            class="form-control @error('material') is-invalid @enderror"
            placeholder="e.g. Grade 4 Titanium, SLA Active">
        @error('material')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="lot_number" class="form-label fw-semibold">Lot / Batch #</label>
        <input type="text" id="lot_number" name="lot_number"
            value="{{ old('lot_number', $implant->lot_number ?? '') }}"
            class="form-control @error('lot_number') is-invalid @enderror"
            placeholder="e.g. LOT-2026-X8">
        @error('lot_number')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="connection_type" class="form-label fw-semibold">Connection System</label>
        <input type="text" id="connection_type" name="connection_type"
            value="{{ old('connection_type', $implant->connection_type ?? '') }}"
            class="form-control @error('connection_type') is-invalid @enderror"
            placeholder="e.g. Internal Hex, Morse Taper">
        @error('connection_type')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="status" class="form-label fw-semibold">Catalog Status</label>
        <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
            <option value="active" {{ old('status', $implant->status ?? 'active') === 'active' ? 'selected' : '' }}>Active (Available for sales)</option>
            <option value="inactive" {{ old('status', $implant->status ?? '') === 'inactive' ? 'selected' : '' }}>Inactive / Discontinued</option>
        </select>
        @error('status')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Pricing & Stock --}}
    <div class="col-12 mt-4">
        <h6 class="fw-bold text-dark border-bottom pb-2">
            <i class="bi bi-cash-stack text-success me-1"></i> Pricing &amp; Stock Management
        </h6>
    </div>

    <div class="col-md-3">
        <label for="purchase_price" class="form-label fw-semibold">Cost / Purchase Price <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text bg-light text-muted">PKR</span>
            <input type="number" step="0.01" min="0" id="purchase_price" name="purchase_price"
                value="{{ old('purchase_price', $implant->purchase_price ?? '') }}"
                class="form-control @error('purchase_price') is-invalid @enderror" required
                placeholder="0.00">
        </div>
        <div class="form-text small">Our acquisition cost.</div>
        @error('purchase_price')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label for="sale_price" class="form-label fw-semibold">Selling Price <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text bg-light text-muted">PKR</span>
            <input type="number" step="0.01" min="0" id="sale_price" name="sale_price"
                value="{{ old('sale_price', $implant->sale_price ?? '') }}"
                class="form-control @error('sale_price') is-invalid @enderror" required
                placeholder="0.00">
        </div>
        <div class="form-text small">Price to external doctors or clinic charge.</div>
        @error('sale_price')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    @if (!isset($implant))
        <div class="col-md-3">
            <label for="stock_quantity" class="form-label fw-semibold">Initial Stock Quantity <span class="text-danger">*</span></label>
            <input type="number" min="0" id="stock_quantity" name="stock_quantity"
                value="{{ old('stock_quantity', 0) }}"
                class="form-control @error('stock_quantity') is-invalid @enderror" required>
            <div class="form-text small">Units currently on hand in clinic.</div>
            @error('stock_quantity')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    @else
        <div class="col-md-3">
            <label class="form-label fw-semibold">Current Stock on Hand</label>
            <div class="form-control bg-light fw-bold text-{{ $implant->stockStatusColor() }}">
                {{ $implant->stock_quantity }} units
                <span class="badge bg-{{ $implant->stockStatusColor() }} ms-2">{{ $implant->stockStatusLabel() }}</span>
            </div>
            <div class="form-text small">Use "+ Quick Restock" on details page to adjust stock.</div>
        </div>
    @endif

    <div class="col-md-3">
        <label for="min_stock_alert" class="form-label fw-semibold">Low Stock Alert Threshold <span class="text-danger">*</span></label>
        <input type="number" min="0" id="min_stock_alert" name="min_stock_alert"
            value="{{ old('min_stock_alert', $implant->min_stock_alert ?? 5) }}"
            class="form-control @error('min_stock_alert') is-invalid @enderror" required>
        <div class="form-text small">Triggers warning when stock falls to this amount.</div>
        @error('min_stock_alert')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <label for="notes" class="form-label fw-semibold">Notes / Clinical Description</label>
        <textarea id="notes" name="notes" rows="2"
            class="form-control @error('notes') is-invalid @enderror"
            placeholder="e.g. Suitable for immediate placement, bone level insertion, high primary stability...">{{ old('notes', $implant->notes ?? '') }}</textarea>
        @error('notes')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
