@extends('layouts.app')

@section('title', 'Clinic Settings')

@section('content')

    <div class="card shadow-sm" style="max-width: 720px;">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Clinic Settings</h5>
        </div>

        <div class="card-body">

            <div class="alert alert-light border small text-muted py-2">
                <i class="bi bi-info-circle me-1"></i>
                These details appear in the sidebar and on printed <strong>invoices</strong> and
                <strong>receipts</strong>.
            </div>

            <form method="POST" action="{{ route('settings.update') }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="clinic_name" class="form-label">Clinic Name <span class="text-danger">*</span></label>
                    <input type="text" id="clinic_name" name="clinic_name"
                        value="{{ old('clinic_name', \App\Models\Setting::get('clinic_name', config('app.name', 'Skin Clinic'))) }}"
                        class="form-control @error('clinic_name') is-invalid @enderror" required>
                    @error('clinic_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="address" class="form-label">Address</label>
                    <textarea id="address" name="address" rows="2" class="form-control @error('address') is-invalid @enderror">{{ old('address', \App\Models\Setting::get('address')) }}</textarea>
                    @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" id="phone" name="phone"
                            value="{{ old('phone', \App\Models\Setting::get('phone')) }}"
                            class="form-control @error('phone') is-invalid @enderror">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email"
                            value="{{ old('email', \App\Models\Setting::get('email')) }}"
                            class="form-control @error('email') is-invalid @enderror">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="currency" class="form-label">Currency Code</label>
                        <input type="text" id="currency" name="currency"
                            value="{{ old('currency', \App\Models\Setting::get('currency')) }}"
                            class="form-control @error('currency') is-invalid @enderror" placeholder="e.g. PKR, USD">
                        <div class="form-text">Stored for future use (e.g. on printed documents).</div>
                        @error('currency')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Save Settings
                </button>
            </form>
        </div>
    </div>

@endsection
