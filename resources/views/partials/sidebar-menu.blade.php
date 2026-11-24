@php
    $prefix = $prefix ?? 'nav';
@endphp

<ul class="nav nav-pills flex-column gap-1">

    {{-- 1. Main / Dashboard (Standalone link) --}}
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
            <i class="bi bi-speedometer2 me-2"></i> Dashboard
        </a>
    </li>

    {{-- 2. Patients & Care (Dropdown Group) --}}
    @php
        $isPatientsActive = request()->routeIs('patients.*', 'appointments.*', 'patient-histories.index', 'patient-histories.create', 'patient-histories.show', 'patient-histories.edit');
    @endphp
    <li class="nav-item">
        <a class="group-toggle {{ $isPatientsActive ? 'active-group' : 'collapsed' }}"
            href="#{{ $prefix }}-patients-group" data-bs-toggle="collapse" role="button"
            aria-expanded="{{ $isPatientsActive ? 'true' : 'false' }}"
            aria-controls="{{ $prefix }}-patients-group">
            <span class="d-flex align-items-center">
                <i class="bi bi-people me-2 text-primary"></i> Patients &amp; Care
            </span>
            <i class="bi bi-chevron-down submenu-arrow"></i>
        </a>
        <div class="collapse {{ $isPatientsActive ? 'show' : '' }}" id="{{ $prefix }}-patients-group">
            <ul class="submenu-list">
                <li>
                    <a class="sub-nav-link {{ request()->routeIs('patients.index', 'patients.show') ? 'active' : '' }}"
                        href="{{ route('patients.index') }}">
                        <i class="bi bi-person-lines-fill me-2"></i> {{ auth()->user()->isDoctor() ? 'My Patients' : 'All Patients' }}
                    </a>
                </li>
                @if (auth()->user()->hasRole('admin', 'doctor', 'receptionist', 'staff'))
                    <li>
                        <a class="sub-nav-link {{ request()->routeIs('patients.create') ? 'active' : '' }}"
                            href="{{ route('patients.create') }}">
                            <i class="bi bi-person-plus me-2"></i> Add Patient
                        </a>
                    </li>
                    <li>
                        <a class="sub-nav-link {{ request()->routeIs('appointments.*') ? 'active' : '' }}"
                            href="{{ route('appointments.index') }}">
                            <i class="bi bi-calendar-check me-2"></i> {{ auth()->user()->isDoctor() ? 'My Schedule' : 'Appointments' }}
                        </a>
                    </li>
                @endif
                <li>
                    <a class="sub-nav-link {{ request()->routeIs('patient-histories.index', 'patient-histories.show', 'patient-histories.edit') ? 'active' : '' }}"
                        href="{{ route('patient-histories.index') }}">
                        <i class="bi bi-clock-history me-2"></i> {{ auth()->user()->isDoctor() ? 'My Pre-App Patients' : 'Patient History (Pre-App)' }}
                    </a>
                </li>
                @if (auth()->user()->hasRole('admin', 'doctor', 'receptionist', 'staff'))
                    <li>
                        <a class="sub-nav-link {{ request()->routeIs('patient-histories.create') ? 'active' : '' }}"
                            href="{{ route('patient-histories.create') }}">
                            <i class="bi bi-folder-plus me-2"></i> Add Pre-App Record
                        </a>
                    </li>
                @endif
            </ul>
        </div>
    </li>

    {{-- 3. Dental Treatments (Dropdown Group) --}}
    @if (auth()->user()->hasRole('admin', 'doctor', 'receptionist', 'staff'))
        @php
            $isTreatmentsActive = request()->routeIs('treatments.*', 'patient-treatments.*');
        @endphp
        <li class="nav-item">
            <a class="group-toggle {{ $isTreatmentsActive ? 'active-group' : 'collapsed' }}"
                href="#{{ $prefix }}-treatments-group" data-bs-toggle="collapse" role="button"
                aria-expanded="{{ $isTreatmentsActive ? 'true' : 'false' }}"
                aria-controls="{{ $prefix }}-treatments-group">
                <span class="d-flex align-items-center">
                    <i class="bi bi-bandaid me-2 text-info"></i> Dental Treatments
                </span>
                <i class="bi bi-chevron-down submenu-arrow"></i>
            </a>
            <div class="collapse {{ $isTreatmentsActive ? 'show' : '' }}" id="{{ $prefix }}-treatments-group">
                <ul class="submenu-list">
                    @if (auth()->user()->hasRole('admin', 'doctor', 'staff'))
                        <li>
                            <a class="sub-nav-link {{ request()->routeIs('treatments.*') ? 'active' : '' }}"
                                href="{{ route('treatments.index') }}">
                                <i class="bi bi-card-checklist me-2"></i> Treatments Catalog
                            </a>
                        </li>
                    @endif
                    <li>
                        <a class="sub-nav-link {{ request()->routeIs('patient-treatments.*') ? 'active' : '' }}"
                            href="{{ route('patient-treatments.index') }}">
                            <i class="bi bi-clipboard2-check me-2"></i> {{ auth()->user()->isDoctor() ? 'My Treatments' : 'Patient Treatments' }}
                        </a>
                    </li>
                </ul>
            </div>
        </li>
    @endif

    {{-- 4. Dental Implants (Dropdown Group - Separate Module) --}}
    @if (auth()->user()->hasRole('admin', 'accountant', 'staff'))
        @php
            $isImplantsActive = request()->routeIs('implants.*', 'implant-buyers.*', 'implant-sales.*', 'implant-payments.*');
        @endphp
        <li class="nav-item">
            <a class="group-toggle {{ $isImplantsActive ? 'active-group' : 'collapsed' }}"
                href="#{{ $prefix }}-implants-group" data-bs-toggle="collapse" role="button"
                aria-expanded="{{ $isImplantsActive ? 'true' : 'false' }}"
                aria-controls="{{ $prefix }}-implants-group">
                <span class="d-flex align-items-center">
                    <i class="bi bi-pin-angle me-2 text-warning"></i> Dental Implants
                </span>
                <i class="bi bi-chevron-down submenu-arrow"></i>
            </a>
            <div class="collapse {{ $isImplantsActive ? 'show' : '' }}" id="{{ $prefix }}-implants-group">
                <ul class="submenu-list">
                    <li>
                        <a class="sub-nav-link {{ request()->routeIs('implants.*') ? 'active' : '' }}"
                            href="{{ route('implants.index') }}">
                            <i class="bi bi-box-seam me-2"></i> Implants Inventory
                        </a>
                    </li>
                    <li>
                        <a class="sub-nav-link {{ request()->routeIs('implant-buyers.*') ? 'active' : '' }}"
                            href="{{ route('implant-buyers.index') }}">
                            <i class="bi bi-person-badge me-2"></i> Doctors &amp; Clinics
                        </a>
                    </li>
                    <li>
                        <a class="sub-nav-link {{ request()->routeIs('implant-sales.create') ? 'active' : '' }}"
                            href="{{ route('implant-sales.create') }}">
                            <i class="bi bi-cart-plus me-2"></i> New Implant Sale
                        </a>
                    </li>
                    <li>
                        <a class="sub-nav-link {{ request()->routeIs('implant-sales.index', 'implant-sales.show') ? 'active' : '' }}"
                            href="{{ route('implant-sales.index') }}">
                            <i class="bi bi-receipt me-2"></i> Sales Invoices
                        </a>
                    </li>
                    <li>
                        <a class="sub-nav-link {{ request()->routeIs('implant-sales.outstanding') ? 'active' : '' }}"
                            href="{{ route('implant-sales.outstanding') }}">
                            <i class="bi bi-exclamation-circle me-2 text-danger"></i> Doctor Outstandings
                        </a>
                    </li>
                    <li>
                        <a class="sub-nav-link {{ request()->routeIs('implant-payments.*') ? 'active' : '' }}"
                            href="{{ route('implant-payments.index') }}">
                            <i class="bi bi-journal-check me-2"></i> Payments Ledger
                        </a>
                    </li>
                </ul>
            </div>
        </li>
    @endif

    {{-- 5. Billing & Finance (Dropdown Group) --}}
    @if (auth()->user()->hasRole('admin', 'accountant', 'receptionist'))
        @php
            $isBillingActive = request()->routeIs('invoices.*', 'payments.*', 'patient-histories.outstanding');
        @endphp
        <li class="nav-item">
            <a class="group-toggle {{ $isBillingActive ? 'active-group' : 'collapsed' }}"
                href="#{{ $prefix }}-billing-group" data-bs-toggle="collapse" role="button"
                aria-expanded="{{ $isBillingActive ? 'true' : 'false' }}"
                aria-controls="{{ $prefix }}-billing-group">
                <span class="d-flex align-items-center">
                    <i class="bi bi-credit-card me-2 text-success"></i> Billing &amp; Finance
                </span>
                <i class="bi bi-chevron-down submenu-arrow"></i>
            </a>
            <div class="collapse {{ $isBillingActive ? 'show' : '' }}" id="{{ $prefix }}-billing-group">
                <ul class="submenu-list">
                    <li>
                        <a class="sub-nav-link {{ request()->routeIs('invoices.*') ? 'active' : '' }}"
                            href="{{ route('invoices.index') }}">
                            <i class="bi bi-receipt me-2"></i> Invoices
                        </a>
                    </li>
                    <li>
                        <a class="sub-nav-link {{ request()->routeIs('payments.index', 'payments.create', 'payments.edit') ? 'active' : '' }}"
                            href="{{ route('payments.index') }}">
                            <i class="bi bi-cash-coin me-2"></i> Payments
                        </a>
                    </li>
                    <li>
                        <a class="sub-nav-link {{ request()->routeIs('payments.outstanding') ? 'active' : '' }}"
                            href="{{ route('payments.outstanding') }}">
                            <i class="bi bi-exclamation-circle me-2"></i> Outstanding Dues
                        </a>
                    </li>
                    <li>
                        <a class="sub-nav-link {{ request()->routeIs('patient-histories.outstanding') ? 'active' : '' }}"
                            href="{{ route('patient-histories.outstanding') }}">
                            <i class="bi bi-clock-history me-2 text-warning"></i> Pre-App Outstandings
                        </a>
                    </li>
                </ul>
            </div>
        </li>
    @endif

    {{-- 5. Expenses (Dropdown Group) --}}
    @if (auth()->user()->hasRole('admin', 'accountant'))
        @php
            $isExpensesActive = request()->routeIs('expenses.*', 'expense-categories.*');
        @endphp
        <li class="nav-item">
            <a class="group-toggle {{ $isExpensesActive ? 'active-group' : 'collapsed' }}"
                href="#{{ $prefix }}-expenses-group" data-bs-toggle="collapse" role="button"
                aria-expanded="{{ $isExpensesActive ? 'true' : 'false' }}"
                aria-controls="{{ $prefix }}-expenses-group">
                <span class="d-flex align-items-center">
                    <i class="bi bi-wallet2 me-2 text-warning"></i> Expenses
                </span>
                <i class="bi bi-chevron-down submenu-arrow"></i>
            </a>
            <div class="collapse {{ $isExpensesActive ? 'show' : '' }}" id="{{ $prefix }}-expenses-group">
                <ul class="submenu-list">
                    <li>
                        <a class="sub-nav-link {{ request()->routeIs('expenses.*') ? 'active' : '' }}"
                            href="{{ route('expenses.index') }}">
                            <i class="bi bi-wallet2 me-2"></i> Expenses List
                        </a>
                    </li>
                    <li>
                        <a class="sub-nav-link {{ request()->routeIs('expense-categories.*') ? 'active' : '' }}"
                            href="{{ route('expense-categories.index') }}">
                            <i class="bi bi-collection me-2"></i> Expense Categories
                        </a>
                    </li>
                </ul>
            </div>
        </li>
    @endif

    {{-- 6. Dental Pharmacy & Stock (Dropdown Group - Hidden for now) --}}
    @php
        $showPharmacyStock = false;
    @endphp
    @if ($showPharmacyStock && auth()->user()->hasRole('admin', 'staff'))
        @php
            $isInventoryActive = request()->routeIs(
                'medicines.*',
                'medicine-categories.*',
                'stock.*',
                'suppliers.*',
                'medicine-usages.*',
            );
        @endphp
        <li class="nav-item">
            <a class="group-toggle {{ $isInventoryActive ? 'active-group' : 'collapsed' }}"
                href="#{{ $prefix }}-inventory-group" data-bs-toggle="collapse" role="button"
                aria-expanded="{{ $isInventoryActive ? 'true' : 'false' }}"
                aria-controls="{{ $prefix }}-inventory-group">
                <span class="d-flex align-items-center">
                    <i class="bi bi-capsule me-2 text-danger"></i> Pharmacy &amp; Stock
                </span>
                <i class="bi bi-chevron-down submenu-arrow"></i>
            </a>
            <div class="collapse {{ $isInventoryActive ? 'show' : '' }}" id="{{ $prefix }}-inventory-group">
                <ul class="submenu-list">
                    @if (auth()->user()->isAdmin())
                        <li>
                            <a class="sub-nav-link {{ request()->routeIs('medicines.*') ? 'active' : '' }}"
                                href="{{ route('medicines.index') }}">
                                <i class="bi bi-basket2 me-2"></i> Medicines &amp; Materials
                            </a>
                        </li>
                        <li>
                            <a class="sub-nav-link {{ request()->routeIs('medicine-categories.*') ? 'active' : '' }}"
                                href="{{ route('medicine-categories.index') }}">
                                <i class="bi bi-tags me-2"></i> Categories
                            </a>
                        </li>
                        <li>
                            <a class="sub-nav-link {{ request()->routeIs('stock.*') ? 'active' : '' }}"
                                href="{{ route('stock.index') }}">
                                <i class="bi bi-box-seam me-2"></i> Stock Registry
                            </a>
                        </li>
                        <li>
                            <a class="sub-nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}"
                                href="{{ route('suppliers.index') }}">
                                <i class="bi bi-truck me-2"></i> Dental Suppliers
                            </a>
                        </li>
                    @endif
                    <li>
                        <a class="sub-nav-link {{ request()->routeIs('medicine-usages.*') ? 'active' : '' }}"
                            href="{{ route('medicine-usages.index') }}">
                            <i class="bi bi-capsule me-2"></i> Material Usage
                        </a>
                    </li>
                </ul>
            </div>
        </li>
    @endif

    {{-- 7. Reports & Analytics (Dropdown Group) --}}
    @if (auth()->user()->hasRole('admin', 'accountant'))
        @php
            $isReportsActive = request()->routeIs('reports.*');
        @endphp
        <li class="nav-item">
            <a class="group-toggle {{ $isReportsActive ? 'active-group' : 'collapsed' }}"
                href="#{{ $prefix }}-reports-group" data-bs-toggle="collapse" role="button"
                aria-expanded="{{ $isReportsActive ? 'true' : 'false' }}"
                aria-controls="{{ $prefix }}-reports-group">
                <span class="d-flex align-items-center">
                    <i class="bi bi-graph-up me-2 text-info"></i> Reports &amp; Analytics
                </span>
                <i class="bi bi-chevron-down submenu-arrow"></i>
            </a>
            <div class="collapse {{ $isReportsActive ? 'show' : '' }}" id="{{ $prefix }}-reports-group">
                <ul class="submenu-list">
                    <li>
                        <a class="sub-nav-link {{ request()->routeIs('reports.patients') ? 'active' : '' }}"
                            href="{{ route('reports.patients') }}">
                            <i class="bi bi-people me-2"></i> Patient Report
                        </a>
                    </li>
                    <li>
                        <a class="sub-nav-link {{ request()->routeIs('reports.payments') ? 'active' : '' }}"
                            href="{{ route('reports.payments') }}">
                            <i class="bi bi-cash-coin me-2"></i> Payment Report
                        </a>
                    </li>
                    <li>
                        <a class="sub-nav-link {{ request()->routeIs('reports.expenses') ? 'active' : '' }}"
                            href="{{ route('reports.expenses') }}">
                            <i class="bi bi-wallet2 me-2"></i> Expense Report
                        </a>
                    </li>
                    {{-- <li>
                        <a class="sub-nav-link {{ request()->routeIs('reports.inventory') ? 'active' : '' }}"
                            href="{{ route('reports.inventory') }}">
                            <i class="bi bi-basket2 me-2"></i> Inventory Report
                        </a>
                    </li>
                    <li>
                        <a class="sub-nav-link {{ request()->routeIs('reports.usage') ? 'active' : '' }}"
                            href="{{ route('reports.usage') }}">
                            <i class="bi bi-capsule me-2"></i> Usage Report
                        </a>
                    </li> --}}
                    <li>
                        <a class="sub-nav-link {{ request()->routeIs('reports.financial') ? 'active' : '' }}"
                            href="{{ route('reports.financial') }}">
                            <i class="bi bi-graph-up me-2"></i> Financial Summary
                        </a>
                    </li>
                </ul>
            </div>
        </li>
    @endif

    {{-- 8. Administration (Dropdown Group - Admin Only) --}}
    @if (auth()->user()->isAdmin())
        @php
            $isAdminActive = request()->routeIs('users.*', 'settings.*');
        @endphp
        <li class="nav-item">
            <a class="group-toggle {{ $isAdminActive ? 'active-group' : 'collapsed' }}"
                href="#{{ $prefix }}-admin-group" data-bs-toggle="collapse" role="button"
                aria-expanded="{{ $isAdminActive ? 'true' : 'false' }}"
                aria-controls="{{ $prefix }}-admin-group">
                <span class="d-flex align-items-center">
                    <i class="bi bi-gear me-2 text-secondary"></i> Administration
                </span>
                <i class="bi bi-chevron-down submenu-arrow"></i>
            </a>
            <div class="collapse {{ $isAdminActive ? 'show' : '' }}" id="{{ $prefix }}-admin-group">
                <ul class="submenu-list">
                    <li>
                        <a class="sub-nav-link {{ request()->routeIs('users.index', 'users.create', 'users.edit') ? 'active' : '' }}"
                            href="{{ route('users.index') }}">
                            <i class="bi bi-people me-2"></i> Users
                        </a>
                    </li>
                    <li>
                        <a class="sub-nav-link {{ request()->routeIs('users.roles') ? 'active' : '' }}"
                            href="{{ route('users.roles') }}">
                            <i class="bi bi-shield-lock me-2"></i> Roles &amp; Permissions
                        </a>
                    </li>
                    <li>
                        <a class="sub-nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}"
                            href="{{ route('settings.index') }}">
                            <i class="bi bi-sliders me-2"></i> Clinic Settings
                        </a>
                    </li>
                </ul>
            </div>
        </li>
    @endif

</ul>
