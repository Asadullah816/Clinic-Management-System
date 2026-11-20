<ul class="nav nav-pills flex-column gap-1">

    {{-- Main / Dashboard (all roles) --}}
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
            <i class="bi bi-speedometer2 me-2"></i> Dashboard
        </a>
    </li>

    {{-- Patients Group --}}
    <li class="nav-item">
        <span class="nav-link text-uppercase small text-secondary px-2 pb-0">
            <i class="bi bi-people me-1"></i> Patients &amp; Care
        </span>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('patients.index', 'patients.show') ? 'active' : '' }} ps-4"
            href="{{ route('patients.index') }}">
            <i class="bi bi-person-lines-fill me-2"></i> All Patients
        </a>
    </li>
    @if (auth()->user()->hasRole('admin', 'receptionist', 'staff'))
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('patients.create') ? 'active' : '' }} ps-4"
                href="{{ route('patients.create') }}">
                <i class="bi bi-person-plus me-2"></i> Add Patient
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('appointments.*') ? 'active' : '' }} ps-4"
                href="{{ route('appointments.index') }}">
                <i class="bi bi-calendar-check me-2"></i> Appointments
            </a>
        </li>
    @endif

    {{-- Treatments Group --}}
    @if (auth()->user()->hasRole('admin', 'receptionist', 'staff'))
        <li class="nav-item">
            <span class="nav-link text-uppercase small text-secondary px-2 pb-0">
                <i class="bi bi-bandaid me-1"></i> Dental Treatments
            </span>
        </li>
        @if (auth()->user()->hasRole('admin', 'staff'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('treatments.index', 'treatments.create', 'treatments.edit') ? 'active' : '' }} ps-4"
                    href="{{ route('treatments.index') }}">
                    <i class="bi bi-card-checklist me-2"></i> Treatments Catalog
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

    {{-- Billing Group --}}
    @if (auth()->user()->hasRole('admin', 'accountant', 'receptionist'))
        <li class="nav-item">
            <span class="nav-link text-uppercase small text-secondary px-2 pb-0">
                <i class="bi bi-credit-card me-1"></i> Billing &amp; Finance
            </span>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('invoices.*') ? 'active' : '' }} ps-4"
                href="{{ route('invoices.index') }}">
                <i class="bi bi-receipt me-2"></i> Invoices
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('payments.index', 'payments.create', 'payments.edit') ? 'active' : '' }} ps-4"
                href="{{ route('payments.index') }}">
                <i class="bi bi-cash-coin me-2"></i> Payments
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('payments.outstanding') ? 'active' : '' }} ps-4"
                href="{{ route('payments.outstanding') }}">
                <i class="bi bi-exclamation-circle me-2"></i> Outstanding Dues
            </a>
        </li>
    @endif

    {{-- Expenses Group --}}
    @if (auth()->user()->hasRole('admin', 'accountant'))
        <li class="nav-item">
            <span class="nav-link text-uppercase small text-secondary px-2 pb-0">
                <i class="bi bi-wallet2 me-1"></i> Expenses
            </span>
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

    {{-- Dental Pharmacy & Inventory --}}
    @if (auth()->user()->hasRole('admin', 'staff'))
        <li class="nav-item">
            <span class="nav-link text-uppercase small text-secondary px-2 pb-0">
                <i class="bi bi-capsule me-1"></i> Dental Pharmacy &amp; Stock
            </span>
        </li>
    @endif
    @if (auth()->user()->isAdmin())
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('medicines.index', 'medicines.show', 'medicines.create', 'medicines.edit') ? 'active' : '' }} ps-4"
                href="{{ route('medicines.index') }}">
                <i class="bi bi-basket2 me-2"></i> Medicines &amp; Materials
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('medicine-categories.index', 'medicine-categories.create', 'medicine-categories.edit') ? 'active' : '' }} ps-4"
                href="{{ route('medicine-categories.index') }}">
                <i class="bi bi-tags me-2"></i> Categories
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('stock.index', 'stock.create') ? 'active' : '' }} ps-4"
                href="{{ route('stock.index') }}">
                <i class="bi bi-box-seam me-2"></i> Stock Registry
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('suppliers.index', 'suppliers.create', 'suppliers.edit') ? 'active' : '' }} ps-4"
                href="{{ route('suppliers.index') }}">
                <i class="bi bi-truck me-2"></i> Dental Suppliers
            </a>
        </li>
    @endif
    @if (auth()->user()->hasRole('admin', 'staff'))
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('medicine-usages.*') ? 'active' : '' }} ps-4"
                href="{{ route('medicine-usages.index') }}">
                <i class="bi bi-capsule me-2"></i> Material Usage
            </a>
        </li>
    @endif

    {{-- Reports Group --}}
    @if (auth()->user()->hasRole('admin', 'accountant'))
        <li class="nav-item">
            <span class="nav-link text-uppercase small text-secondary px-2 pb-0">
                <i class="bi bi-graph-up me-1"></i> Reports &amp; Analytics
            </span>
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
                <i class="bi bi-capsule me-2"></i> Usage Report
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('reports.financial') ? 'active' : '' }} ps-4"
                href="{{ route('reports.financial') }}">
                <i class="bi bi-graph-up me-2"></i> Financial Summary
            </a>
        </li>
    @endif

    {{-- System / Administration (admin only) --}}
    @if (auth()->user()->isAdmin())
        <li class="nav-item">
            <span class="nav-link text-uppercase small text-secondary px-2 pb-0">
                <i class="bi bi-gear me-1"></i> Administration
            </span>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('users.index', 'users.create', 'users.edit') ? 'active' : '' }} ps-4"
                href="{{ route('users.index') }}">
                <i class="bi bi-people me-2"></i> Users
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('users.roles') ? 'active' : '' }} ps-4"
                href="{{ route('users.roles') }}">
                <i class="bi bi-shield-lock me-2"></i> Roles
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }} ps-4"
                href="{{ route('settings.index') }}">
                <i class="bi bi-sliders me-2"></i> Clinic Settings
            </a>
        </li>
    @endif

</ul>
