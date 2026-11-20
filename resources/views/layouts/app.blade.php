<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') | {{ \App\Models\Setting::get('clinic_name', config('app.name', 'Dr Badshah Afsar')) }}</title>

    {{-- Favicon --}}
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">

    {{-- Google Fonts: Plus Jakarta Sans --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --cms-font: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            --cms-primary: #0284c7;
            --cms-primary-dark: #0369a1;
            --cms-primary-light: #e0f2fe;
            --cms-bg: #f8fafc;
            --cms-card-border: #e2e8f0;
            --cms-text-main: #0f172a;
            --cms-text-muted: #64748b;
            --cms-sidebar-bg: linear-gradient(180deg, #0b132b 0%, #111e38 50%, #172545 100%);
        }

        body {
            font-family: var(--cms-font);
            background-color: var(--cms-bg);
            color: var(--cms-text-main);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* ---------------- Sidebar ---------------- */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: 256px;
            overflow-y: auto;
            z-index: 1030;
            background: var(--cms-sidebar-bg);
            border-right: 1px solid rgba(255, 255, 255, 0.06);
            box-shadow: 4px 0 24px rgba(11, 19, 43, 0.12);
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.18) transparent;
        }

        .sidebar::-webkit-scrollbar {
            width: 5px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.18);
            border-radius: 9999px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.35);
        }

        .sidebar .nav-link {
            color: #94a3b8;
            font-size: 0.865rem;
            font-weight: 500;
            border-radius: 0.5rem;
            padding: 0.5rem 0.75rem;
            transition: all 0.16s ease-in-out;
            display: flex;
            align-items: center;
        }

        .sidebar a.nav-link:hover {
            color: #ffffff;
            background-color: rgba(255, 255, 255, 0.08);
            transform: translateX(3px);
        }

        .sidebar a.nav-link.active {
            color: #ffffff !important;
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%) !important;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.35);
            transform: none;
        }

        .sidebar span.nav-link {
            cursor: default;
            color: #64748b !important;
            font-size: 0.68rem !important;
            font-weight: 700 !important;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            padding: 0.85rem 0.5rem 0.25rem 0.5rem;
            margin-top: 0.35rem;
            background: transparent !important;
            transform: none !important;
            box-shadow: none !important;
        }

        /* ---------------- Sidebar Dropdown Groups ---------------- */
        .sidebar .group-toggle,
        .mobile-sidebar-drawer .group-toggle {
            cursor: pointer;
            user-select: none;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: #cbd5e1;
            font-size: 0.84rem;
            font-weight: 600;
            padding: 0.52rem 0.75rem;
            border-radius: 0.5rem;
            transition: all 0.18s ease-in-out;
            background: transparent;
            text-decoration: none;
        }

        .sidebar .group-toggle:hover,
        .mobile-sidebar-drawer .group-toggle:hover {
            color: #ffffff;
            background-color: rgba(255, 255, 255, 0.08);
            transform: translateX(2px);
        }

        .sidebar .group-toggle.active-group,
        .mobile-sidebar-drawer .group-toggle.active-group {
            color: #38bdf8;
            background-color: rgba(56, 189, 248, 0.12);
        }

        .sidebar .group-toggle .submenu-arrow,
        .mobile-sidebar-drawer .group-toggle .submenu-arrow {
            font-size: 0.72rem;
            transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            opacity: 0.75;
        }

        .sidebar .group-toggle[aria-expanded="true"] .submenu-arrow,
        .mobile-sidebar-drawer .group-toggle[aria-expanded="true"] .submenu-arrow {
            transform: rotate(180deg);
            opacity: 1;
        }

        .sidebar .submenu-list,
        .mobile-sidebar-drawer .submenu-list {
            list-style: none;
            padding-left: 0.75rem;
            margin: 0.2rem 0 0.35rem 0.75rem;
            border-left: 1.5px solid rgba(255, 255, 255, 0.12);
            display: flex;
            flex-direction: column;
            gap: 0.2rem;
        }

        .sidebar .sub-nav-link,
        .mobile-sidebar-drawer .sub-nav-link {
            display: flex;
            align-items: center;
            color: #94a3b8;
            font-size: 0.815rem;
            font-weight: 500;
            padding: 0.38rem 0.65rem;
            border-radius: 0.4rem;
            text-decoration: none;
            transition: all 0.15s ease-in-out;
        }

        .sidebar .sub-nav-link:hover,
        .mobile-sidebar-drawer .sub-nav-link:hover {
            color: #ffffff;
            background-color: rgba(255, 255, 255, 0.08);
            transform: translateX(3px);
        }

        .sidebar .sub-nav-link.active,
        .mobile-sidebar-drawer .sub-nav-link.active {
            color: #ffffff !important;
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%) !important;
            font-weight: 600;
            box-shadow: 0 3px 10px rgba(2, 132, 199, 0.35);
            transform: none;
        }

        /* ---------------- Mobile Sidebar Drawer ---------------- */
        .mobile-sidebar-drawer {
            width: 310px !important;
            max-width: 86vw !important;
            background: linear-gradient(180deg, #090e17 0%, #0d1527 50%, #111e38 100%) !important;
            border-right: 1px solid rgba(255, 255, 255, 0.08) !important;
            border-top-right-radius: 20px !important;
            border-bottom-right-radius: 20px !important;
            box-shadow: 12px 0 36px rgba(0, 0, 0, 0.5) !important;
        }

        .mobile-drawer-close {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: #94a3b8;
            transition: all 0.2s ease;
        }

        .mobile-drawer-close:hover {
            background: rgba(255, 255, 255, 0.15);
            color: #ffffff;
            transform: scale(1.05);
        }

        .mobile-user-card {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.05);
        }

        .mobile-sidebar-drawer .offcanvas-body {
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.18) transparent;
        }

        .mobile-sidebar-drawer .offcanvas-body::-webkit-scrollbar {
            width: 4px;
        }

        .mobile-sidebar-drawer .offcanvas-body::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.18);
            border-radius: 9999px;
        }

        .mobile-sidebar-drawer .nav-link {
            color: #94a3b8;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 0.625rem;
            padding: 0.58rem 0.85rem;
            display: flex;
            align-items: center;
            transition: all 0.16s ease-in-out;
        }

        .mobile-sidebar-drawer a.nav-link:hover {
            color: #ffffff;
            background-color: rgba(255, 255, 255, 0.08);
            transform: translateX(3px);
        }

        .mobile-sidebar-drawer a.nav-link.active {
            color: #ffffff !important;
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%) !important;
            font-weight: 600;
            box-shadow: 0 4px 14px rgba(2, 132, 199, 0.4);
            transform: none;
        }

        .mobile-sidebar-drawer span.nav-link {
            cursor: default;
            color: #64748b !important;
            font-size: 0.68rem !important;
            font-weight: 700 !important;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            padding: 0.75rem 0.5rem 0.25rem 0.5rem;
            margin-top: 0.4rem;
            background: transparent !important;
            transform: none !important;
            box-shadow: none !important;
        }

        .offcanvas-backdrop.show {
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            background-color: rgba(9, 14, 23, 0.68);
        }

        /* ---------------- Main Layout ---------------- */
        .main-wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        @media (min-width: 992px) {
            .main-wrapper {
                margin-left: 256px;
            }
        }

        /* ---------------- Navbar ---------------- */
        .app-navbar {
            background-color: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--cms-card-border);
            padding: 0.65rem 1.25rem;
            z-index: 1020;
        }

        /* ---------------- Cards ---------------- */
        .card {
            border: 1px solid var(--cms-card-border);
            border-radius: 0.875rem;
            background-color: #ffffff;
            box-shadow: 0 1px 3px 0 rgba(15, 23, 42, 0.04), 0 1px 2px -1px rgba(15, 23, 42, 0.03);
            transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }

        a.text-decoration-none > .card:hover,
        .card.card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -3px rgba(15, 23, 42, 0.07), 0 4px 6px -4px rgba(15, 23, 42, 0.04);
        }

        .card-header {
            background-color: #ffffff;
            border-bottom: 1px solid #f1f5f9;
            font-weight: 600;
            padding: 0.95rem 1.25rem;
            color: #0f172a;
        }

        .card-body {
            padding: 1.25rem;
        }

        .card-footer {
            background-color: #ffffff;
            border-top: 1px solid #f1f5f9;
            padding: 0.85rem 1.25rem;
        }

        /* ---------------- Stat Cards ---------------- */
        .stat-card {
            border: 1px solid var(--cms-card-border) !important;
            border-radius: 1rem !important;
            background: #ffffff;
            transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 24px -4px rgba(15, 23, 42, 0.08), 0 4px 6px -2px rgba(15, 23, 42, 0.03);
            border-color: #cbd5e1 !important;
        }

        .stat-icon-box {
            width: 44px;
            height: 44px;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: transform 0.2s ease;
        }

        .stat-card:hover .stat-icon-box {
            transform: scale(1.08);
        }

        /* Border accents for legacy metric cards */
        .card.border-start.border-4 {
            border: 1px solid var(--cms-card-border) !important;
            border-left-width: 4px !important;
            border-top-left-radius: 0.875rem !important;
            border-bottom-left-radius: 0.875rem !important;
        }
        .card.border-start.border-primary { border-left-color: #0284c7 !important; }
        .card.border-start.border-success { border-left-color: #10b981 !important; }
        .card.border-start.border-danger  { border-left-color: #ef4444 !important; }
        .card.border-start.border-warning { border-left-color: #f59e0b !important; }
        .card.border-start.border-info    { border-left-color: #06b6d4 !important; }

        /* ---------------- Tables ---------------- */
        .table {
            --bs-table-bg: transparent;
            margin-bottom: 0;
            font-size: 0.875rem;
        }

        .table thead th {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #64748b;
            background-color: #f8fafc;
            border-bottom: 1px solid var(--cms-card-border);
            padding: 0.75rem 1rem;
            white-space: nowrap;
        }

        .table tbody td {
            padding: 0.85rem 1rem;
            color: #334155;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }

        .table-hover tbody tr:hover td {
            background-color: #f8fafc;
        }

        /* ---------------- Buttons & Inputs ---------------- */
        .form-control, .form-select {
            border-radius: 0.5rem;
            border: 1px solid #cbd5e1;
            font-size: 0.875rem;
            padding: 0.5rem 0.85rem;
            color: #1e293b;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--cms-primary);
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
        }

        .btn {
            font-weight: 500;
            font-size: 0.875rem;
            border-radius: 0.5rem;
            padding: 0.45rem 0.85rem;
            transition: all 0.15s ease-in-out;
        }

        .btn-primary {
            background-color: var(--cms-primary);
            border-color: var(--cms-primary);
        }

        .btn-primary:hover {
            background-color: var(--cms-primary-dark);
            border-color: var(--cms-primary-dark);
        }

        .badge {
            font-weight: 600;
            font-size: 0.74rem;
            padding: 0.35em 0.65em;
            border-radius: 0.375rem;
            letter-spacing: 0.02em;
        }
    </style>
</head>

<body>

    @include('partials.sidebar')

    <div class="main-wrapper">
        @include('partials.navbar')

        <main class="container-fluid px-4 py-3">
            @include('partials.flash-messages')

            @yield('content')
        </main>

        <footer class="text-center text-muted py-3 mt-auto small">
            &copy; {{ date('Y') }} {{ \App\Models\Setting::get('clinic_name', config('app.name', 'Dr Badshah Afsar')) }}
            &middot; Dental Clinic Management System
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    {{-- Sidebar Scroll Position & Auto-Scroll into View --}}
    <script>
        (function () {
            function autoScrollSidebar() {
                const sidebar = document.querySelector('.sidebar');
                if (!sidebar) return;

                const activeLink = sidebar.querySelector('a.nav-link.active');
                const savedScroll = sessionStorage.getItem('cms_sidebar_scroll');

                // First apply saved scroll if available
                if (savedScroll !== null) {
                    sidebar.scrollTop = parseInt(savedScroll, 10);
                }

                // If an active link exists, verify it is comfortably in view; if not, center it
                if (activeLink) {
                    const itemTop = activeLink.offsetTop;
                    const itemBottom = itemTop + activeLink.offsetHeight;
                    const viewTop = sidebar.scrollTop;
                    const viewBottom = viewTop + sidebar.clientHeight;

                    if (itemTop < viewTop + 50 || itemBottom > viewBottom - 50) {
                        const targetScroll = itemTop - Math.floor(sidebar.clientHeight / 2) + Math.floor(activeLink.offsetHeight / 2);
                        sidebar.scrollTop = Math.max(0, targetScroll);
                    }
                }

                // Save scroll position on user scroll (debounced)
                let scrollDebounce;
                sidebar.addEventListener('scroll', function () {
                    clearTimeout(scrollDebounce);
                    scrollDebounce = setTimeout(function () {
                        sessionStorage.setItem('cms_sidebar_scroll', sidebar.scrollTop);
                    }, 50);
                }, { passive: true });

                // Save immediately when clicking any sidebar link
                sidebar.querySelectorAll('a.nav-link').forEach(function (link) {
                    link.addEventListener('click', function () {
                        sessionStorage.setItem('cms_sidebar_scroll', sidebar.scrollTop);
                    });
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', autoScrollSidebar);
            } else {
                autoScrollSidebar();
            }
            window.addEventListener('load', autoScrollSidebar);

            // Mobile offcanvas support
            const mobileSidebar = document.getElementById('mobileSidebar');
            if (mobileSidebar) {
                mobileSidebar.addEventListener('shown.bs.offcanvas', function () {
                    const body = mobileSidebar.querySelector('.offcanvas-body');
                    const activeMobile = mobileSidebar.querySelector('a.nav-link.active');
                    if (body && activeMobile) {
                        const target = activeMobile.offsetTop - Math.floor(body.clientHeight / 2) + Math.floor(activeMobile.offsetHeight / 2);
                        body.scrollTop = Math.max(0, target);
                    }
                });
            }
        })();
    </script>
</body>

</html>
