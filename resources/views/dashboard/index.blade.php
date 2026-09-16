@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0">Welcome back, {{ auth()->user()->name }}</h4>
            <div class="text-muted">{{ now()->format('l, d F Y') }}</div>
        </div>
        <span class="badge text-bg-primary text-capitalize">{{ auth()->user()->role }}</span>
    </div>

    {{-- ==================== Patients (all roles) ==================== --}}
    <div class="row g-3 mb-3">
        <div class="col-6 col-md-3">
            <a href="{{ route('patients.index') }}" class="text-decoration-none">
                <div class="card shadow-sm h-100 text-dark">
                    <div class="card-body d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0"
                            style="width: 48px; height: 48px;">
                            <i class="bi bi-people fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Total Patients</div>
                            <div class="fs-4 fw-bold">{{ number_format($totalPatients) }}</div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-6 col-md-3">
            <a href="{{ route('patients.index') }}" class="text-decoration-none">
                <div class="card shadow-sm h-100 text-dark">
                    <div class="card-body d-flex align-items-center">
                        <div class="bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0"
                            style="width: 48px; height: 48px;">
                            <i class="bi bi-person-plus fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small">New Patients Today</div>
                            <div class="fs-4 fw-bold">{{ number_format($todaysPatients) }}</div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-6 col-md-3">
            <a href="{{ route('patients.index') }}" class="text-decoration-none">
                <div class="card shadow-sm h-100 text-dark">
                    <div class="card-body d-flex align-items-center">
                        <div class="bg-secondary bg-opacity-10 text-secondary rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0"
                            style="width: 48px; height: 48px;">
                            <i class="bi bi-calendar-month fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small">New This Month</div>
                            <div class="fs-4 fw-bold">{{ number_format($monthsPatients) }}</div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        @if ($showAppointments)
            <div class="col-6 col-md-3">
                <a href="{{ route('appointments.index', ['date_filter' => 'today']) }}" class="text-decoration-none">
                    <div class="card shadow-sm h-100 text-dark">
                        <div class="card-body d-flex align-items-center">
                            <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0"
                                style="width: 48px; height: 48px;">
                                <i class="bi bi-calendar-check fs-4"></i>
                            </div>
                            <div>
                                <div class="text-muted small">Appointments Today</div>
                                <div class="fs-4 fw-bold">{{ number_format($todaysAppointments->count()) }}</div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endif
    </div>

    {{-- ==================== Financial (admin, accountant) ==================== --}}
    @if ($showFinancial)
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
            <h6 class="text-muted text-uppercase small mb-0">Financial Summary</h6>

            <form method="GET" action="{{ route('dashboard') }}" class="mb-0">
                <select name="period" class="form-select form-select-sm" onchange="this.form.submit()">
                    @foreach ($periods as $value => $label)
                        <option value="{{ $value }}" {{ $period === $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-6 col-md-3">
                <div class="card shadow-sm h-100 border-start border-success border-4">
                    <div class="card-body">
                        <div class="text-muted small">Revenue — {{ $periodLabel }}</div>
                        <div class="fs-5 fw-bold text-success">{{ number_format($periodRevenue, 2) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card shadow-sm h-100 border-start border-danger border-4">
                    <div class="card-body">
                        <div class="text-muted small">Expenses — {{ $periodLabel }}</div>
                        <div class="fs-5 fw-bold text-danger">{{ number_format($periodExpenses, 2) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <a href="{{ route('payments.outstanding') }}" class="text-decoration-none">
                    <div class="card shadow-sm h-100 text-dark border-start border-warning border-4">
                        <div class="card-body">
                            <div class="text-muted small">Total Outstanding <span class="text-lowercase">(all time)</span>
                            </div>
                            <div class="fs-5 fw-bold {{ $totalOutstanding > 0 ? 'text-danger' : '' }}">
                                {{ number_format($totalOutstanding, 2) }}
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <div
                    class="card shadow-sm h-100 {{ $periodProfit >= 0 ? 'border-start border-success border-4' : 'border-start border-danger border-4' }}">
                    <div class="card-body">
                        <div class="text-muted small">Profit — {{ $periodLabel }}</div>
                        <div class="fs-5 fw-bold {{ $periodProfit >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ number_format($periodProfit, 2) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ==================== Inventory (admin, staff) ==================== --}}
    @if ($showInventory)
        <h6 class="text-muted text-uppercase small mb-2">Inventory</h6>
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <a href="{{ route('medicines.index') }}" class="text-decoration-none">
                    <div class="card shadow-sm h-100 text-dark border-start border-primary border-4">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small">Total Products</div>
                                <div class="fs-5 fw-bold">{{ number_format($totalProducts) }}</div>
                            </div>
                            <i class="bi bi-basket2 fs-2 text-primary opacity-25"></i>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="{{ route('medicines.index', ['low_stock' => 1]) }}" class="text-decoration-none">
                    <div class="card shadow-sm h-100 text-dark border-start border-warning border-4">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small">Low Stock Products</div>
                                <div class="fs-5 fw-bold {{ $lowStockCount > 0 ? 'text-warning' : '' }}">
                                    {{ number_format($lowStockCount) }}
                                </div>
                            </div>
                            <i class="bi bi-exclamation-triangle fs-2 text-warning opacity-25"></i>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="{{ route('medicines.index', ['expired' => 1]) }}" class="text-decoration-none">
                    <div class="card shadow-sm h-100 text-dark border-start border-danger border-4">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small">Expired Products</div>
                                <div class="fs-5 fw-bold {{ $expiredCount > 0 ? 'text-danger' : '' }}">
                                    {{ number_format($expiredCount) }}
                                </div>
                            </div>
                            <i class="bi bi-x-circle fs-2 text-danger opacity-25"></i>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    @endif

    {{-- ==================== Today's appointments (admin, receptionist, staff) ==================== --}}
    @if ($showAppointments)
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0">
                    Today's Appointments
                    <span class="badge text-bg-secondary ms-1">{{ $todaysAppointments->count() }}</span>
                </h5>
                <a href="{{ route('appointments.index', ['date_filter' => 'today']) }}"
                    class="btn btn-sm btn-outline-primary">
                    View all <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>

            <div class="card-body">
                @if ($todaysAppointments->isEmpty())
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-calendar-check fs-1 d-block mb-2"></i>
                        No appointments scheduled for today.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Time</th>
                                    <th>Patient</th>
                                    <th>Treatment</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($todaysAppointments as $appointment)
                                    <tr>
                                        <td class="fw-semibold">{{ $appointment->appointment_time }}</td>
                                        <td>
                                            <a href="{{ route('patients.show', $appointment->patient) }}"
                                                class="text-decoration-none">
                                                {{ $appointment->patient->full_name }}
                                            </a>
                                        </td>
                                        <td>{{ $appointment->treatment->name ?? '—' }}</td>
                                        <td>
                                            <span class="badge text-bg-{{ $appointment->statusColor() }}">
                                                {{ $appointment->statusLabel() }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('appointments.show', $appointment) }}"
                                                class="btn btn-sm btn-outline-secondary" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('appointments.edit', $appointment) }}"
                                                class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
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
