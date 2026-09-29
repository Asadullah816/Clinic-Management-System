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

<nav class="navbar app-navbar sticky-top mb-3">
    <div class="container-fluid px-1">

        {{-- Mobile Hamburger --}}
        <button class="btn btn-outline-secondary btn-sm d-lg-none me-2 shadow-none border-0" type="button" data-bs-toggle="offcanvas"
            data-bs-target="#mobileSidebar" aria-label="Toggle navigation">
            <i class="bi bi-list fs-4"></i>
        </button>

        {{-- Page Title --}}
        <div class="d-flex align-items-center">
            <span class="fs-5 fw-bold text-truncate" style="color: #0f172a; letter-spacing: -0.01em;">
                @yield('title', 'Dashboard')
            </span>
        </div>

        {{-- Right Controls --}}
        <div class="ms-auto d-flex align-items-center gap-2 gap-sm-3">
            {{-- User Chip --}}
            <div class="d-flex align-items-center gap-2 px-2 py-1 rounded-pill bg-light border">
                <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm"
                    style="width: 28px; height: 28px; font-size: 0.75rem; background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);">
                    {{ $initial }}
                </div>
                <div class="d-none d-md-block text-start lh-1 pe-1">
                    <span class="d-block fw-semibold text-dark small" style="font-size: 0.825rem;">{{ auth()->user()->name }}</span>
                    <span class="text-muted text-capitalize" style="font-size: 0.68rem;">{{ $role }}</span>
                </div>
                <span class="badge {{ $roleBadgeClass }} text-capitalize d-md-none" style="font-size: 0.65rem;">
                    {{ $role }}
                </span>
            </div>

            {{-- Logout Button --}}
            <form method="POST" action="{{ route('logout') }}" class="d-inline mb-0">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1 py-1 px-2"
                    title="Log out">
                    <i class="bi bi-box-arrow-right"></i>
                    <span class="d-none d-sm-inline">Logout</span>
                </button>
            </form>
        </div>
    </div>
</nav>

