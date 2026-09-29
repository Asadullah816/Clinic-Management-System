{{-- Expects: $category (null on create) --}}

<div class="row">
    <div class="col-md-8 mb-3">
        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
        <input type="text" id="name" name="name" value="{{ old('name', $category->name ?? '') }}"
            class="form-control @error('name') is-invalid @enderror" placeholder="e.g. Cream, Serum, Injection" required>
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4 mb-3">
        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
        <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
            <option value="active" {{ old('status', $category->status ?? 'active') === 'active' ? 'selected' : '' }}>Active
            </option>
            <option value="inactive" {{ old('status', $category->status ?? 'active') === 'inactive' ? 'selected' : '' }}>
                Inactive</option>
        </select>
        @error('status')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="mb-3">
    <label for="description" class="form-label">Description</label>
    <textarea id="description" name="description" rows="3"
        class="form-control @error('description') is-invalid @enderror" placeholder="Optional notes or details about this category">{{ old('description', $category->description ?? '') }}</textarea>
    @error('description')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
