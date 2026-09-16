{{-- Expects: $treatment (null on create) --}}

<div class="mb-3">
    <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
    <input type="text" id="name" name="name" value="{{ old('name', $treatment->name ?? '') }}"
        class="form-control @error('name') is-invalid @enderror" placeholder="e.g. Hydrafacial" required>
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="description" class="form-label">Description</label>
    <textarea id="description" name="description" rows="2"
        class="form-control @error('description') is-invalid @enderror">{{ old('description', $treatment->description ?? '') }}</textarea>
    @error('description')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label for="price" class="form-label">Price <span class="text-danger">*</span></label>
        <input type="number" step="0.01" min="0" id="price" name="price"
            value="{{ old('price', $treatment->price ?? '') }}"
            class="form-control @error('price') is-invalid @enderror" required>
        @error('price')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4 mb-3">
        <label for="duration" class="form-label">Duration (minutes)</label>
        <input type="number" step="1" min="1" id="duration" name="duration"
            value="{{ old('duration', $treatment->duration ?? '') }}"
            class="form-control @error('duration') is-invalid @enderror">
        @error('duration')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4 mb-3">
        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
        <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
            <option value="active" {{ old('status', $treatment->status ?? 'active') === 'active' ? 'selected' : '' }}>
                Active</option>
            <option value="inactive"
                {{ old('status', $treatment->status ?? 'active') === 'inactive' ? 'selected' : '' }}>Inactive</option>
        </select>
        <div class="form-text">Inactive treatments disappear from booking dropdowns.</div>
        @error('status')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
