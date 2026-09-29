{{-- Desktop sidebar --}}
<nav class="sidebar d-none d-lg-flex flex-column p-3">
    <a href="{{ route('dashboard') }}" class="d-flex align-items-center text-white text-decoration-none mb-3 px-1 py-1">
        <div class="brand-icon rounded-3 d-flex align-items-center justify-content-center me-2 flex-shrink-0"
            style="width: 38px; height: 38px; background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%); box-shadow: 0 4px 12px rgba(2, 132, 199, 0.4);">
            <i class="bi bi-heart-pulse text-white fs-5"></i>
        </div>
        <div class="overflow-hidden">
            <span class="fs-6 fw-bold d-block text-white text-truncate lh-sm" title="{{ \App\Models\Setting::get('clinic_name', config('app.name', 'Mayar skin care & Aesthethic clinic')) }}">
                {{ \App\Models\Setting::get('clinic_name', config('app.name', 'Mayar skin care & Aesthethic clinic')) }}
            </span>
            <span class="small text-secondary" style="font-size: 0.7rem; letter-spacing: 0.04em;">Clinic Management</span>
        </div>
    </a>

    @include('partials.sidebar-menu')
</nav>

{{-- Mobile sidebar --}}
<div class="offcanvas offcanvas-start" tabindex="-1" id="mobileSidebar" style="background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);">
    <div class="offcanvas-header border-bottom border-secondary border-opacity-25 px-3 py-3">
        <div class="d-flex align-items-center">
            <div class="brand-icon rounded-3 d-flex align-items-center justify-content-center me-2"
                style="width: 36px; height: 36px; background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%);">
                <i class="bi bi-heart-pulse text-white fs-6"></i>
            </div>
            <span class="fs-6 fw-bold text-white text-truncate" style="max-width: 210px;">
                {{ \App\Models\Setting::get('clinic_name', config('app.name', 'Mayar skin care & Aesthethic clinic')) }}
            </span>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-3">
        @include('partials.sidebar-menu')
    </div>
</div>
