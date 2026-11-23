{{-- Desktop sidebar --}}
<nav class="sidebar d-none d-lg-flex flex-column p-3">
    <a href="{{ route('dashboard') }}" class="d-flex align-items-center text-white text-decoration-none mb-3 px-1 py-1">
        <div class="brand-icon rounded-3 d-flex align-items-center justify-content-center me-2 flex-shrink-0"
            style="width: 38px; height: 38px; background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%); box-shadow: 0 4px 12px rgba(2, 132, 199, 0.4);">
            @include('partials.dental-logo', ['width' => 22, 'height' => 22])
        </div>
        <div class="overflow-hidden">
            <span class="fs-6 fw-bold d-block text-white text-truncate lh-sm" title="{{ \App\Models\Setting::get('clinic_name', config('app.name', 'Dr Badshah Afsar')) }}">
                {{ \App\Models\Setting::get('clinic_name', config('app.name', 'Dr Badshah Afsar')) }}
            </span>
            <span class="small text-info fw-semibold" style="font-size: 0.7rem; letter-spacing: 0.04em;">Dental Surgery &amp; Care</span>
        </div>
    </a>

    @include('partials.sidebar-menu', ['prefix' => 'desk'])
</nav>

{{-- Premium Mobile Sidebar Drawer --}}
@php
    $role = auth()->user()->role;
    $roleBadgeClass = match($role) {
        'admin' => 'bg-danger-subtle text-danger border border-danger-subtle',
        'accountant' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
        'receptionist' => 'bg-info-subtle text-info-emphasis border border-info-subtle',
        default => 'bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle',
    };
    $initial = strtoupper(substr(auth()->user()->name, 0, 1));
@endphp

<div class="offcanvas offcanvas-start mobile-sidebar-drawer border-0" tabindex="-1" id="mobileSidebar" aria-labelledby="mobileSidebarLabel">
    {{-- Drawer Header --}}
    <div class="offcanvas-header d-flex align-items-center justify-content-between px-3 py-3 border-bottom border-white border-opacity-10">
        <div class="d-flex align-items-center overflow-hidden me-2">
            <div class="brand-icon rounded-3 d-flex align-items-center justify-content-center me-2 flex-shrink-0"
                style="width: 38px; height: 38px; background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%); box-shadow: 0 4px 14px rgba(2, 132, 199, 0.45);">
                @include('partials.dental-logo', ['width' => 22, 'height' => 22])
            </div>
            <div class="overflow-hidden">
                <span id="mobileSidebarLabel" class="fs-6 fw-bold text-white d-block text-truncate lh-sm">
                    {{ \App\Models\Setting::get('clinic_name', config('app.name', 'Dr Badshah Afsar')) }}
                </span>
                <span class="badge bg-info-subtle text-info fw-semibold" style="font-size: 0.65rem; letter-spacing: 0.04em;">
                    Dental Surgery &amp; Clinic
                </span>
            </div>
        </div>
        <button type="button" class="btn mobile-drawer-close d-flex align-items-center justify-content-center shadow-none p-0" data-bs-dismiss="offcanvas" aria-label="Close menu">
            <i class="bi bi-x-lg fs-6"></i>
        </button>
    </div>

    {{-- Drawer Body --}}
    <div class="offcanvas-body d-flex flex-column p-3">

        {{-- Mobile User Card --}}
        <div class="mobile-user-card p-3 mb-3 rounded-3 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center overflow-hidden">
                <div class="position-relative me-2 flex-shrink-0">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm"
                        style="width: 36px; height: 36px; font-size: 0.85rem; background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); border: 2px solid rgba(255, 255, 255, 0.2);">
                        {{ $initial }}
                    </div>
                    <span class="position-absolute bottom-0 end-0 bg-success rounded-circle border border-dark"
                        style="width: 10px; height: 10px;" title="Online"></span>
                </div>
                <div class="overflow-hidden">
                    <span class="d-block text-white fw-semibold small text-truncate">{{ auth()->user()->name }}</span>
                    <span class="badge {{ $roleBadgeClass }} text-capitalize px-2 py-0" style="font-size: 0.65rem;">
                        {{ $role }}
                    </span>
                </div>
            </div>
            @if (auth()->user()->isAdmin())
                <a href="{{ route('settings.index') }}" class="btn btn-sm btn-outline-light border-0 opacity-75 hover-opacity-100 p-1" title="Settings">
                    <i class="bi bi-gear fs-5"></i>
                </a>
            @endif
        </div>

        {{-- Quick Mobile Shortcuts --}}
        @if (auth()->user()->hasRole('admin', 'receptionist', 'staff'))
            <div class="row g-2 mb-3">
                <div class="col-6">
                    <a href="{{ route('patients.create') }}" class="btn btn-sm btn-primary w-100 d-flex align-items-center justify-content-center gap-1 py-2 rounded-3 shadow-sm" style="font-size: 0.8rem;">
                        <i class="bi bi-person-plus-fill"></i>
                        <span>New Patient</span>
                    </a>
                </div>
                <div class="col-6">
                    <a href="{{ route('appointments.create') }}" class="btn btn-sm btn-outline-info w-100 d-flex align-items-center justify-content-center gap-1 py-2 rounded-3" style="font-size: 0.8rem; border-color: rgba(56, 189, 248, 0.4);">
                        <i class="bi bi-calendar-plus"></i>
                        <span>New Appt</span>
                    </a>
                </div>
            </div>
        @endif

        {{-- Navigation Menu --}}
        <div class="mobile-menu-wrapper flex-grow-1">
            @include('partials.sidebar-menu', ['prefix' => 'mob'])
        </div>

        {{-- Mobile Drawer Footer --}}
        <div class="mobile-drawer-footer pt-3 mt-3 border-top border-white border-opacity-10">
            <form method="POST" action="{{ route('logout') }}" class="mb-2">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-danger w-100 d-flex align-items-center justify-content-center gap-2 py-2 rounded-3">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Log Out</span>
                </button>
            </form>
            <div class="text-center text-secondary small" style="font-size: 0.7rem; opacity: 0.75;">
                &copy; {{ date('Y') }} Dr Badshah Afsar &middot; Dental Clinic
            </div>
        </div>

    </div>
</div>
