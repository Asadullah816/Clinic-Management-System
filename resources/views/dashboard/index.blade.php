@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

    {{-- Welcome Banner / Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color: #0f172a; letter-spacing: -0.02em;">Welcome back, {{ auth()->user()->name }} 👋</h4>
            <div class="text-muted small">
                <i class="bi bi-calendar-event me-1"></i> {{ now()->format('l, d F Y') }}
                <span class="mx-2">&middot;</span>
                <i class="bi bi-geo-alt me-1"></i> {{ \App\Models\Setting::get('clinic_name', 'Mayar skin care & Aesthethic clinic') }}
            </div>
        </div>
        <div class="mt-2 mt-sm-0">
            <span class="badge bg-primary-subtle text-primary-emphasis px-3 py-2 rounded-pill text-capitalize fw-semibold" style="font-size: 0.8rem;">
                <i class="bi bi-shield-check me-1"></i> {{ auth()->user()->role }}
            </span>
        </div>
    </div>

    {{-- ==================== Patients Overview (all roles) ==================== --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <a href="{{ route('patients.index') }}" class="text-decoration-none text-dark d-block h-100">
                <div class="card stat-card shadow-sm h-100">
                    <div class="card-body p-3 p-xl-4 d-flex flex-column justify-content-between">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted fw-semibold small text-uppercase" style="letter-spacing: 0.05em; font-size: 0.72rem;">
                                Total Patients
                            </span>
                            <div class="stat-icon-box" style="background-color: #eff6ff; color: #2563eb;">
                                <i class="bi bi-people fs-5"></i>
                            </div>
                        </div>
                        <div>
                            <h3 class="fw-bold mb-0" style="color: #0f172a; letter-spacing: -0.02em;">
                                {{ number_format($totalPatients) }}
                            </h3>
                        </div>
                        <div class="mt-3 pt-2 border-top d-flex align-items-center justify-content-between small text-muted" style="border-color: #f1f5f9 !important;">
                            <span class="text-truncate">All registered records</span>
                            <i class="bi bi-chevron-right text-muted" style="font-size: 0.75rem;"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-6 col-lg-3">
            <a href="{{ route('patients.index') }}" class="text-decoration-none text-dark d-block h-100">
                <div class="card stat-card shadow-sm h-100">
                    <div class="card-body p-3 p-xl-4 d-flex flex-column justify-content-between">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted fw-semibold small text-uppercase" style="letter-spacing: 0.05em; font-size: 0.72rem;">
                                New Today
                            </span>
                            <div class="stat-icon-box" style="background-color: #ecfeff; color: #0891b2;">
                                <i class="bi bi-person-plus fs-5"></i>
                            </div>
                        </div>
                        <div>
                            <h3 class="fw-bold mb-0" style="color: #0f172a; letter-spacing: -0.02em;">
                                {{ number_format($todaysPatients) }}
                            </h3>
                        </div>
                        <div class="mt-3 pt-2 border-top d-flex align-items-center justify-content-between small text-muted" style="border-color: #f1f5f9 !important;">
                            <span class="text-truncate">Joined today</span>
                            <i class="bi bi-chevron-right text-muted" style="font-size: 0.75rem;"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-6 col-lg-3">
            <a href="{{ route('patients.index') }}" class="text-decoration-none text-dark d-block h-100">
                <div class="card stat-card shadow-sm h-100">
                    <div class="card-body p-3 p-xl-4 d-flex flex-column justify-content-between">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted fw-semibold small text-uppercase" style="letter-spacing: 0.05em; font-size: 0.72rem;">
                                New This Month
                            </span>
                            <div class="stat-icon-box" style="background-color: #f5f3ff; color: #7c3aed;">
                                <i class="bi bi-calendar2-heart fs-5"></i>
                            </div>
                        </div>
                        <div>
                            <h3 class="fw-bold mb-0" style="color: #0f172a; letter-spacing: -0.02em;">
                                {{ number_format($monthsPatients) }}
                            </h3>
                        </div>
                        <div class="mt-3 pt-2 border-top d-flex align-items-center justify-content-between small text-muted" style="border-color: #f1f5f9 !important;">
                            <span class="text-truncate">Current month</span>
                            <i class="bi bi-chevron-right text-muted" style="font-size: 0.75rem;"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        @if ($showAppointments)
            <div class="col-6 col-lg-3">
                <a href="{{ route('appointments.index', ['date_filter' => 'today']) }}" class="text-decoration-none text-dark d-block h-100">
                    <div class="card stat-card shadow-sm h-100">
                        <div class="card-body p-3 p-xl-4 d-flex flex-column justify-content-between">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-muted fw-semibold small text-uppercase" style="letter-spacing: 0.05em; font-size: 0.72rem;">
                                    Appointments Today
                                </span>
                                <div class="stat-icon-box" style="background-color: #fefce8; color: #ca8a04;">
                                    <i class="bi bi-calendar-check fs-5"></i>
                                </div>
                            </div>
                            <div>
                                <h3 class="fw-bold mb-0" style="color: #0f172a; letter-spacing: -0.02em;">
                                    {{ number_format($todaysAppointments->count()) }}
                                </h3>
                            </div>
                            <div class="mt-3 pt-2 border-top d-flex align-items-center justify-content-between small text-muted" style="border-color: #f1f5f9 !important;">
                                <span class="text-truncate">Scheduled for today</span>
                                <i class="bi bi-chevron-right text-muted" style="font-size: 0.75rem;"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endif
    </div>

    {{-- ==================== Financial Overview (admin, accountant) ==================== --}}
    @if ($showFinancial)
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center gap-2">
                <div class="p-1 rounded-2 bg-success bg-opacity-10 text-success">
                    <i class="bi bi-cash-stack fs-5"></i>
                </div>
                <h6 class="fw-bold text-dark mb-0">Financial Overview</h6>
            </div>

            <form method="GET" action="{{ route('dashboard') }}" class="mb-0">
                <select name="period" class="form-select form-select-sm shadow-none" style="min-width: 140px; border-color: #cbd5e1;" onchange="this.form.submit()">
                    @foreach ($periods as $value => $label)
                        <option value="{{ $value }}" {{ $period === $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>

        <div class="row g-3 mb-4">
            {{-- Revenue --}}
            <div class="col-6 col-lg-3">
                <div class="card stat-card shadow-sm h-100">
                    <div class="card-body p-3 p-xl-4 d-flex flex-column justify-content-between">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted fw-semibold small text-uppercase" style="letter-spacing: 0.05em; font-size: 0.72rem;">
                                Revenue
                            </span>
                            <div class="stat-icon-box" style="background-color: #ecfdf5; color: #059669;">
                                <i class="bi bi-graph-up-arrow fs-5"></i>
                            </div>
                        </div>
                        <div>
                            <div class="d-flex align-items-baseline gap-1">
                                <span class="text-muted small fw-semibold">PKR</span>
                                <h3 class="fw-bold mb-0 text-success" style="letter-spacing: -0.02em;">
                                    {{ number_format($periodRevenue, 2) }}
                                </h3>
                            </div>
                        </div>
                        <div class="mt-3 pt-2 border-top d-flex align-items-center justify-content-between small text-muted" style="border-color: #f1f5f9 !important;">
                            <span class="badge bg-success-subtle text-success-emphasis rounded-pill px-2">Collected</span>
                            <span class="text-truncate" style="font-size: 0.75rem;">{{ $periodLabel }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Expenses --}}
            <div class="col-6 col-lg-3">
                <a href="{{ route('expenses.index') }}" class="text-decoration-none text-dark d-block h-100">
                    <div class="card stat-card shadow-sm h-100">
                        <div class="card-body p-3 p-xl-4 d-flex flex-column justify-content-between">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-muted fw-semibold small text-uppercase" style="letter-spacing: 0.05em; font-size: 0.72rem;">
                                    Expenses
                                </span>
                                <div class="stat-icon-box" style="background-color: #fef2f2; color: #dc2626;">
                                    <i class="bi bi-wallet2 fs-5"></i>
                                </div>
                            </div>
                            <div>
                                <div class="d-flex align-items-baseline gap-1">
                                    <span class="text-muted small fw-semibold">PKR</span>
                                    <h3 class="fw-bold mb-0 text-danger" style="letter-spacing: -0.02em;">
                                        {{ number_format($periodExpenses, 2) }}
                                    </h3>
                                </div>
                            </div>
                            <div class="mt-3 pt-2 border-top d-flex align-items-center justify-content-between small text-muted" style="border-color: #f1f5f9 !important;">
                                <span class="badge bg-danger-subtle text-danger-emphasis rounded-pill px-2">Spent</span>
                                <span class="text-truncate" style="font-size: 0.75rem;">{{ $periodLabel }}</span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            {{-- Outstanding --}}
            <div class="col-6 col-lg-3">
                <a href="{{ route('payments.outstanding') }}" class="text-decoration-none text-dark d-block h-100">
                    <div class="card stat-card shadow-sm h-100">
                        <div class="card-body p-3 p-xl-4 d-flex flex-column justify-content-between">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-muted fw-semibold small text-uppercase" style="letter-spacing: 0.05em; font-size: 0.72rem;">
                                    Outstanding
                                </span>
                                <div class="stat-icon-box" style="background-color: #fffbeb; color: #d97706;">
                                    <i class="bi bi-hourglass-split fs-5"></i>
                                </div>
                            </div>
                            <div>
                                <div class="d-flex align-items-baseline gap-1">
                                    <span class="text-muted small fw-semibold">PKR</span>
                                    <h3 class="fw-bold mb-0 {{ $totalOutstanding > 0 ? 'text-danger' : 'text-dark' }}" style="letter-spacing: -0.02em;">
                                        {{ number_format($totalOutstanding, 2) }}
                                    </h3>
                                </div>
                            </div>
                            <div class="mt-3 pt-2 border-top d-flex align-items-center justify-content-between small text-muted" style="border-color: #f1f5f9 !important;">
                                <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-2">Due Balance</span>
                                <span class="text-truncate" style="font-size: 0.75rem;">All time pending</span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            {{-- Net Profit --}}
            <div class="col-6 col-lg-3">
                <div class="card stat-card shadow-sm h-100">
                    <div class="card-body p-3 p-xl-4 d-flex flex-column justify-content-between">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted fw-semibold small text-uppercase" style="letter-spacing: 0.05em; font-size: 0.72rem;">
                                Net Profit
                            </span>
                            <div class="stat-icon-box" style="background-color: {{ $periodProfit >= 0 ? '#f0fdf4' : '#fef2f2' }}; color: {{ $periodProfit >= 0 ? '#16a34a' : '#dc2626' }};">
                                <i class="bi {{ $periodProfit >= 0 ? 'bi-piggy-bank' : 'bi-graph-down-arrow' }} fs-5"></i>
                            </div>
                        </div>
                        <div>
                            <div class="d-flex align-items-baseline gap-1">
                                <span class="text-muted small fw-semibold">PKR</span>
                                <h3 class="fw-bold mb-0 {{ $periodProfit >= 0 ? 'text-success' : 'text-danger' }}" style="letter-spacing: -0.02em;">
                                    {{ number_format($periodProfit, 2) }}
                                </h3>
                            </div>
                        </div>
                        <div class="mt-3 pt-2 border-top d-flex align-items-center justify-content-between small text-muted" style="border-color: #f1f5f9 !important;">
                            <span class="badge {{ $periodProfit >= 0 ? 'bg-success-subtle text-success-emphasis' : 'bg-danger-subtle text-danger-emphasis' }} rounded-pill px-2">
                                {{ $periodProfit >= 0 ? 'Net Return' : 'Loss' }}
                            </span>
                            <span class="text-truncate" style="font-size: 0.75rem;">{{ $periodLabel }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ==================== Inventory (admin, staff) ==================== --}}
    @if ($showInventory)
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center gap-2">
                <div class="p-1 rounded-2 bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-boxes fs-5"></i>
                </div>
                <h6 class="fw-bold text-dark mb-0">Inventory &amp; Stock</h6>
            </div>
            <a href="{{ route('medicines.index') }}" class="btn btn-sm btn-outline-secondary py-1 px-2" style="font-size: 0.8rem;">
                View All Items <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <a href="{{ route('medicines.index') }}" class="text-decoration-none text-dark d-block h-100">
                    <div class="card stat-card shadow-sm h-100">
                        <div class="card-body p-3 p-xl-4 d-flex flex-column justify-content-between">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-muted fw-semibold small text-uppercase" style="letter-spacing: 0.05em; font-size: 0.72rem;">
                                    Total Products
                                </span>
                                <div class="stat-icon-box" style="background-color: #f0f9ff; color: #0284c7;">
                                    <i class="bi bi-box-seam fs-5"></i>
                                </div>
                            </div>
                            <div>
                                <h3 class="fw-bold mb-0" style="color: #0f172a; letter-spacing: -0.02em;">
                                    {{ number_format($totalProducts) }}
                                </h3>
                            </div>
                            <div class="mt-3 pt-2 border-top d-flex align-items-center justify-content-between small text-muted" style="border-color: #f1f5f9 !important;">
                                <span>Medicines &amp; items in catalog</span>
                                <i class="bi bi-chevron-right text-muted" style="font-size: 0.75rem;"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-4">
                <a href="{{ route('medicines.index', ['low_stock' => 1]) }}" class="text-decoration-none text-dark d-block h-100">
                    <div class="card stat-card shadow-sm h-100">
                        <div class="card-body p-3 p-xl-4 d-flex flex-column justify-content-between">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-muted fw-semibold small text-uppercase" style="letter-spacing: 0.05em; font-size: 0.72rem;">
                                    Low Stock Alerts
                                </span>
                                <div class="stat-icon-box" style="background-color: #fffbeb; color: #d97706;">
                                    <i class="bi bi-exclamation-triangle fs-5"></i>
                                </div>
                            </div>
                            <div>
                                <h3 class="fw-bold mb-0 {{ $lowStockCount > 0 ? 'text-warning' : 'text-dark' }}" style="letter-spacing: -0.02em;">
                                    {{ number_format($lowStockCount) }}
                                </h3>
                            </div>
                            <div class="mt-3 pt-2 border-top d-flex align-items-center justify-content-between small text-muted" style="border-color: #f1f5f9 !important;">
                                <span class="{{ $lowStockCount > 0 ? 'text-warning fw-semibold' : '' }}">
                                    {{ $lowStockCount > 0 ? 'Needs reordering' : 'Stock levels healthy' }}
                                </span>
                                <i class="bi bi-chevron-right text-muted" style="font-size: 0.75rem;"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-4">
                <a href="{{ route('medicines.index', ['expired' => 1]) }}" class="text-decoration-none text-dark d-block h-100">
                    <div class="card stat-card shadow-sm h-100">
                        <div class="card-body p-3 p-xl-4 d-flex flex-column justify-content-between">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-muted fw-semibold small text-uppercase" style="letter-spacing: 0.05em; font-size: 0.72rem;">
                                    Expired Products
                                </span>
                                <div class="stat-icon-box" style="background-color: #fef2f2; color: #dc2626;">
                                    <i class="bi bi-x-octagon fs-5"></i>
                                </div>
                            </div>
                            <div>
                                <h3 class="fw-bold mb-0 {{ $expiredCount > 0 ? 'text-danger' : 'text-dark' }}" style="letter-spacing: -0.02em;">
                                    {{ number_format($expiredCount) }}
                                </h3>
                            </div>
                            <div class="mt-3 pt-2 border-top d-flex align-items-center justify-content-between small text-muted" style="border-color: #f1f5f9 !important;">
                                <span class="{{ $expiredCount > 0 ? 'text-danger fw-semibold' : '' }}">
                                    {{ $expiredCount > 0 ? 'Action required: disposal' : 'No expired items' }}
                                </span>
                                <i class="bi bi-chevron-right text-muted" style="font-size: 0.75rem;"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    @endif

    {{-- ==================== Today's appointments (admin, receptionist, staff) ==================== --}}
    @if ($showAppointments)
        <div class="card shadow-sm border-0 mb-4" style="border: 1px solid #e2e8f0 !important; border-radius: 1rem;">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="p-1 rounded-2 bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-calendar-check fs-5"></i>
                    </div>
                    <h5 class="fw-bold mb-0 text-dark">
                        Today's Appointments
                        <span class="badge bg-secondary-subtle text-secondary-emphasis rounded-pill ms-2 px-2 py-1" style="font-size: 0.75rem;">
                            {{ $todaysAppointments->count() }}
                        </span>
                    </h5>
                </div>
                <a href="{{ route('appointments.index', ['date_filter' => 'today']) }}"
                    class="btn btn-sm btn-outline-primary px-3 rounded-pill">
                    View all <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>

            <div class="card-body p-0">
                @if ($todaysAppointments->isEmpty())
                    <div class="text-center text-muted py-5">
                        <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                            <i class="bi bi-calendar2-check text-muted fs-2"></i>
                        </div>
                        <h6 class="fw-semibold text-dark mb-1">No appointments scheduled for today</h6>
                        <p class="small text-muted mb-3">All clear for today's schedule, or new patients can be booked anytime.</p>
                        <a href="{{ route('appointments.create') }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-lg me-1"></i> Book New Appointment
                        </a>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Time</th>
                                    <th>Patient</th>
                                    <th>Treatment</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($todaysAppointments as $appointment)
                                    <tr>
                                        <td class="ps-4 fw-semibold text-dark">
                                            <i class="bi bi-clock text-muted me-1"></i> {{ $appointment->appointment_time }}
                                        </td>
                                        <td>
                                            <a href="{{ route('patients.show', $appointment->patient) }}"
                                                class="fw-semibold text-decoration-none text-primary">
                                                {{ $appointment->patient->full_name }}
                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                {{ $appointment->treatment->name ?? '—' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge text-bg-{{ $appointment->statusColor() }} rounded-pill">
                                                {{ $appointment->statusLabel() }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('appointments.show', $appointment) }}"
                                                    class="btn btn-outline-secondary" title="View details">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('appointments.edit', $appointment) }}"
                                                    class="btn btn-outline-primary" title="Edit appointment">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    @endif

@endsection
