{{-- Desktop sidebar --}}
<nav class="sidebar d-none d-lg-flex flex-column text-bg-dark p-3">
    <a href="{{ route('dashboard') }}" class="d-flex align-items-center text-white text-decoration-none mb-4">
        <i class="bi bi-heart-pulse fs-3 me-2"></i>
        <span class="fs-5 fw-bold">{{ config('app.name', 'Skin Clinic') }}</span>
    </a>

    @include('partials.sidebar-menu')
</nav>

{{-- Mobile sidebar --}}
<div class="offcanvas offcanvas-start text-bg-dark" tabindex="-1" id="mobileSidebar">
    <div class="offcanvas-header">
        <span class="fs-5 fw-bold">
            <i class="bi bi-heart-pulse me-2"></i>{{ config('app.name', 'Skin Clinic') }}
        </span>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        @include('partials.sidebar-menu')
    </div>
</div>
