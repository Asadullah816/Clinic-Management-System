<nav class="navbar bg-white shadow-sm sticky-top mb-3">
    <div class="container-fluid px-0">

        {{-- Hamburger (mobile only) --}}
        <button class="btn btn-outline-secondary btn-sm d-lg-none me-2" type="button" data-bs-toggle="offcanvas"
            data-bs-target="#mobileSidebar">
            <i class="bi bi-list fs-5"></i>
        </button>

        <span class="navbar-text fw-semibold text-truncate">
            @yield('title', 'Dashboard')
        </span>

        <div class="ms-auto d-flex align-items-center gap-2">
            <span class="badge text-bg-secondary text-capitalize">{{ auth()->user()->role }}</span>
            <span class="d-none d-sm-inline fw-semibold">{{ auth()->user()->name }}</span>

            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-box-arrow-right me-1"></i> Logout
                </button>
            </form>
        </div>
    </div>
</nav>
