<ul class="nav nav-pills flex-column gap-1">

    {{-- Dashboard (all roles) --}}
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
            <i class="bi bi-speedometer2 me-2"></i> Dashboard
        </a>
    </li>

    {{-- Users (admin only) --}}
    @if (auth()->user()->isAdmin())
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('users.index', 'users.create', 'users.edit') ? 'active' : '' }}"
                href="{{ route('users.index') }}">
                <i class="bi bi-people me-2"></i> Users
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('users.roles') ? 'active' : '' }}"
                href="{{ route('users.roles') }}">
                <i class="bi bi-shield-lock me-2"></i> Roles
            </a>
        </li>
    @endif

    {{-- Patients (all roles can view) --}}
    <li class="nav-item">
        <span class="nav-link text-uppercase small text-secondary px-2 pb-0">Patients</span>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('patients.index', 'patients.show') ? 'active' : '' }} ps-4"
            href="{{ route('patients.index') }}">
            <i class="bi bi-person-lines-fill me-2"></i> All Patients
        </a>
    </li>
    {{-- Appointments (admin, receptionist, staff) --}}
    @if (auth()->user()->hasRole('admin', 'receptionist', 'staff'))
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('appointments.*') ? 'active' : '' }}"
                href="{{ route('appointments.index') }}">
                <i class="bi bi-calendar-check me-2"></i> Appointments
            </a>
        </li>
    @endif
    {{-- Treatments group --}}
    @if (auth()->user()->hasRole('admin', 'receptionist', 'staff'))
        <li class="nav-item">
            <span class="nav-link text-uppercase small text-secondary px-2 pb-0">Treatments</span>
        </li>
        @if (auth()->user()->hasRole('admin', 'staff'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('treatments.index', 'treatments.create', 'treatments.edit') ? 'active' : '' }} ps-4"
                    href="{{ route('treatments.index') }}">
                    <i class="bi bi-bandaid me-2"></i> Treatments
                </a>
            </li>
        @endif
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('patient-treatments.*') ? 'active' : '' }} ps-4"
                href="{{ route('patient-treatments.index') }}">
                <i class="bi bi-clipboard2-check me-2"></i> Patient Treatments
            </a>
        </li>
    @endif
    {{-- Billing group (admin, accountant, receptionist) --}}
    @if (auth()->user()->hasRole('admin', 'accountant', 'receptionist'))
        <li class="nav-item">
            <span class="nav-link text-uppercase small text-secondary px-2 pb-0">Billing</span>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('invoices.*') ? 'active' : '' }} ps-4"
                href="{{ route('invoices.index') }}">
                <i class="bi bi-receipt me-2"></i> Invoices
            </a>
        </li>
        {{-- Phase 8 adds: Payments + Outstanding Payments --}}
    @endif
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('payments.index', 'payments.create', 'payments.edit') ? 'active' : '' }} ps-4"
            href="{{ route('payments.index') }}">
            <i class="bi bi-cash-coin me-2"></i> Payments
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('payments.outstanding') ? 'active' : '' }} ps-4"
            href="{{ route('payments.outstanding') }}">
            <i class="bi bi-exclamation-circle me-2"></i> Outstanding Payments
        </a>
    </li>
    {{-- Add Patient (admin, receptionist, staff) --}}
    @if (auth()->user()->hasRole('admin', 'receptionist', 'staff'))
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('patients.create') ? 'active' : '' }} ps-4"
                href="{{ route('patients.create') }}">
                <i class="bi bi-person-plus me-2"></i> Add Patient
            </a>
        </li>
    @endif
    {{-- Inventory (admin only; staff gains Medicine Usage in Phase 11) --}}
    {{-- @if (auth()->user()->isAdmin())
        <li class="nav-item">
            <span class="nav-link text-uppercase small text-secondary px-2 pb-0">Inventory</span>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('medicines.index', 'medicines.show', 'medicines.create', 'medicines.edit') ? 'active' : '' }} ps-4"
                href="{{ route('medicines.index') }}">
                <i class="bi bi-basket2 me-2"></i> Medicines &amp; Products
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('medicine-categories.index', 'medicine-categories.create', 'medicine-categories.edit') ? 'active' : '' }} ps-4"
                href="{{ route('medicine-categories.index') }}">
                <i class="bi bi-tags me-2"></i> Categories
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('suppliers.index', 'suppliers.create', 'suppliers.edit') ? 'active' : '' }} ps-4"
                href="{{ route('suppliers.index') }}">
                <i class="bi bi-truck me-2"></i> Suppliers
            </a>
        </li>
        @endif --}}
    {{-- Phase 10 adds Stock · Phase 11 adds Medicine Usage --}}
    {{-- Inventory (admin only; staff gains Medicine Usage in Phase 11) --}}
    {{-- Inventory: header + usage for admin & staff; stock-keeping pages admin only --}}
    @if (auth()->user()->hasRole('admin', 'staff'))
        <li class="nav-item">
            <span class="nav-link text-uppercase small text-secondary px-2 pb-0">Inventory</span>
        </li>
    @endif

    @if (auth()->user()->isAdmin())
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('medicines.index', 'medicines.show', 'medicines.create', 'medicines.edit') ? 'active' : '' }} ps-4"
                href="{{ route('medicines.index') }}">
                <i class="bi bi-basket2 me-2"></i> Medicines &amp; Products
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('medicine-categories.index', 'medicine-categories.create', 'medicine-categories.edit') ? 'active' : '' }} ps-4"
                href="{{ route('medicine-categories.index') }}">
                <i class="bi bi-tags me-2"></i> Categories
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('suppliers.index', 'suppliers.create', 'suppliers.edit') ? 'active' : '' }} ps-4"
                href="{{ route('suppliers.index') }}">
                <i class="bi bi-truck me-2"></i> Suppliers
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('stock.index', 'stock.create') ? 'active' : '' }} ps-4"
                href="{{ route('stock.index') }}">
                <i class="bi bi-box-seam me-2"></i> Stock
            </a>
        </li>
    @endif

    @if (auth()->user()->hasRole('admin', 'staff'))
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('medicine-usages.*') ? 'active' : '' }} ps-4"
                href="{{ route('medicine-usages.index') }}">
                <i class="bi bi-capsule me-2"></i> Medicine Usage
            </a>
        </li>
    @endif
    {{-- Expenses (admin, accountant) --}}
    @if (auth()->user()->hasRole('admin', 'accountant'))
        <li class="nav-item">
            <span class="nav-link text-uppercase small text-secondary px-2 pb-0">Expenses</span>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('expenses.index', 'expenses.create', 'expenses.edit') ? 'active' : '' }} ps-4"
                href="{{ route('expenses.index') }}">
                <i class="bi bi-wallet2 me-2"></i> Expenses
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('expense-categories.index', 'expense-categories.create', 'expense-categories.edit') ? 'active' : '' }} ps-4"
                href="{{ route('expense-categories.index') }}">
                <i class="bi bi-collection me-2"></i> Expense Categories
            </a>
        </li>
    @endif
    {{-- Reports (admin, accountant) --}}
    @if (auth()->user()->hasRole('admin', 'accountant'))
        <li class="nav-item">
            <span class="nav-link text-uppercase small text-secondary px-2 pb-0">Reports</span>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('reports.patients') ? 'active' : '' }} ps-4"
                href="{{ route('reports.patients') }}">
                <i class="bi bi-people me-2"></i> Patient Report
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('reports.payments') ? 'active' : '' }} ps-4"
                href="{{ route('reports.payments') }}">
                <i class="bi bi-cash-coin me-2"></i> Payment Report
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('reports.expenses') ? 'active' : '' }} ps-4"
                href="{{ route('reports.expenses') }}">
                <i class="bi bi-wallet2 me-2"></i> Expense Report
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('reports.inventory') ? 'active' : '' }} ps-4"
                href="{{ route('reports.inventory') }}">
                <i class="bi bi-basket2 me-2"></i> Inventory Report
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('reports.usage') ? 'active' : '' }} ps-4"
                href="{{ route('reports.usage') }}">
                <i class="bi bi-capsule me-2"></i> Medicine Usage Report
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('reports.financial') ? 'active' : '' }} ps-4"
                href="{{ route('reports.financial') }}">
                <i class="bi bi-graph-up me-2"></i> Financial Summary
            </a>
        </li>
    @endif

    {{-- ==================================================== --}}
    {{-- Menu sections will be added here, phase by phase:    --}}
    {{--   Phase 5   -> Appointments                          --}}
    {{--   Phase 6   -> Treatments                            --}}
    {{--   Phase 7+8 -> Billing (Invoices, Payments)          --}}
    {{--   Phase 9-11-> Inventory (Medicines, Stock, Usage)   --}}
    {{--   Phase 12  -> Expenses                              --}}
    {{--   Phase 14  -> Reports                               --}}
    {{--   Phase 15  -> Settings                              --}}
    {{-- ==================================================== --}}

</ul>
