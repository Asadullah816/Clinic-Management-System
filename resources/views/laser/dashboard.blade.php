@extends('layouts.app')

@section('title', 'Laser Unit Dashboard')

@section('content')
    {{-- Custom Micro-styles for Laser Aesthetic Excellence --}}
    <style>
        .laser-hero {
            background: linear-gradient(135deg, #0b1329 0%, #0f172a 40%, #1e293b 80%, #0369a1 100%);
            border-radius: 1rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 25px -5px rgba(2, 132, 199, 0.25), 0 8px 10px -6px rgba(15, 23, 42, 0.3);
        }

        .laser-hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 380px;
            height: 380px;
            background: radial-gradient(circle, rgba(56, 189, 248, 0.2) 0%, rgba(2, 132, 199, 0) 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .stat-card-laser {
            border: 1px solid rgba(226, 232, 240, 0.8) !important;
            border-radius: 0.875rem;
            transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1);
            background: #ffffff;
        }

        .stat-card-laser:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 20px -6px rgba(15, 23, 42, 0.08);
            border-color: #cbd5e1 !important;
        }

        .pulse-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #38bdf8;
            box-shadow: 0 0 0 0 rgba(56, 189, 248, 0.7);
            animation: laserPulse 1.8s infinite;
        }

        @keyframes laserPulse {
            0% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(56, 189, 248, 0.7);
            }
            70% {
                transform: scale(1);
                box-shadow: 0 0 0 6px rgba(56, 189, 248, 0);
            }
            100% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(56, 189, 248, 0);
            }
        }

        .period-pill {
            padding: 0.35rem 0.85rem;
            border-radius: 50rem;
            font-size: 0.8rem;
            font-weight: 500;
            color: #64748b;
            text-decoration: none;
            transition: all 0.15s ease-in-out;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }

        .period-pill:hover {
            color: #0284c7;
            background: #f0f9ff;
            border-color: #bae6fd;
        }

        .period-pill.active {
            color: #ffffff;
            background: #0284c7;
            border-color: #0284c7;
            box-shadow: 0 2px 8px rgba(2, 132, 199, 0.35);
        }

        .laser-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.85rem;
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            color: #fff;
        }
    </style>

    {{-- Hero Banner --}}
    <div class="laser-hero text-white p-4 p-md-4 mb-4">
        <div class="row align-items-center g-3">
            <div class="col-12 col-lg-7">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-white bg-opacity-10 text-white border border-white border-opacity-20 px-3 py-1 rounded-pill small">
                        <span class="pulse-dot me-1"></span> Shared Asset &bull; Partner Module
                    </span>
                    <span class="text-white-50 small">
                        <i class="bi bi-clock me-1"></i> {{ now()->format('l, d M Y') }}
                    </span>
                </div>
                <h3 class="fw-bold mb-1" style="letter-spacing: -0.02em;">
                    Laser Aesthetics &amp; Shared Ledger
                </h3>
                <p class="text-white-50 mb-0 small" style="max-width: 580px;">
                    Independent financial records, client records, and operating expenditures specifically partitioned for equipment partners and investors.
                </p>
            </div>
            <div class="col-12 col-lg-5 text-lg-end">
                <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                    <a href="{{ route('laser.sessions.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-1 shadow px-3 py-2 fw-semibold rounded-pill">
                        <i class="bi bi-plus-circle"></i> Record Session
                    </a>
                    <a href="{{ route('laser.patients.create') }}" class="btn btn-light d-inline-flex align-items-center gap-1 shadow-sm px-3 py-2 fw-semibold rounded-pill">
                        <i class="bi bi-person-plus"></i> New Patient
                    </a>
                    <a href="{{ route('laser.reports.financial') }}" class="btn btn-outline-light d-inline-flex align-items-center gap-1 px-3 py-2 rounded-pill">
                        <i class="bi bi-file-earmark-bar-graph"></i> Financial Audit
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Period Filter Tabs --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-calendar3 text-primary"></i>
            <span class="fw-bold text-dark small text-uppercase" style="letter-spacing: 0.05em;">Reporting Period:</span>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">{{ $periodLabel }}</span>
        </div>
        <div class="d-flex flex-wrap gap-1">
            @foreach ($periods as $key => $label)
                <a href="{{ route('laser.dashboard', ['period' => $key]) }}"
                   class="period-pill {{ $period === $key ? 'active' : '' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    {{-- 4 Core Metric Cards --}}
    <div class="row g-3 mb-4">
        {{-- Card 1: Shared Net Operating Profit --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 border-0 rounded-3 text-white overflow-hidden shadow-sm"
                 style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 60%, #075985 100%);">
                <div class="card-body p-3 p-xl-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="small text-uppercase fw-semibold" style="letter-spacing: 0.05em; opacity: 0.9;">
                                Distributable Profit
                            </span>
                            <div class="rounded-circle p-2 bg-white bg-opacity-20 text-white">
                                <i class="bi bi-graph-up-arrow fs-5"></i>
                            </div>
                        </div>
                        <h2 class="fw-bold mb-1" style="letter-spacing: -0.02em;">
                            PKR {{ number_format($periodProfit, 2) }}
                        </h2>
                    </div>
                    <div class="mt-3 pt-2 border-top border-white border-opacity-20 d-flex justify-content-between align-items-center small">
                        <span class="badge bg-white bg-opacity-25 rounded-pill px-2">
                            {{ $profitMargin }}% Return
                        </span>
                        <span style="opacity: 0.85;">{{ $periodLabel }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 2: Collected Revenue --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card stat-card-laser h-100 shadow-sm">
                <div class="card-body p-3 p-xl-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="text-muted small text-uppercase fw-semibold" style="letter-spacing: 0.05em;">
                                Collected Revenue
                            </span>
                            <div class="rounded-circle p-2" style="background-color: #ecfdf5; color: #059669;">
                                <i class="bi bi-cash-stack fs-5"></i>
                            </div>
                        </div>
                        <h2 class="fw-bold mb-1 text-success" style="letter-spacing: -0.02em;">
                            PKR {{ number_format($periodRevenue, 2) }}
                        </h2>
                    </div>
                    <div class="mt-3 pt-2 border-top d-flex justify-content-between align-items-center small text-muted">
                        <span>Billed: PKR {{ number_format($periodBilled, 0) }}</span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Paid</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 3: Operating Expenses --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card stat-card-laser h-100 shadow-sm">
                <div class="card-body p-3 p-xl-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="text-muted small text-uppercase fw-semibold" style="letter-spacing: 0.05em;">
                                Laser Expenses
                            </span>
                            <div class="rounded-circle p-2" style="background-color: #fef2f2; color: #dc2626;">
                                <i class="bi bi-wallet2 fs-5"></i>
                            </div>
                        </div>
                        <h2 class="fw-bold mb-1 text-danger" style="letter-spacing: -0.02em;">
                            PKR {{ number_format($periodExpenses, 2) }}
                        </h2>
                    </div>
                    <div class="mt-3 pt-2 border-top d-flex justify-content-between align-items-center small text-muted">
                        <span>Maintenance &amp; Supplies</span>
                        <a href="{{ route('laser.expenses.index') }}" class="text-decoration-none text-danger fw-semibold">
                            View <i class="bi bi-chevron-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 4: Laser Activity & Clients --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card stat-card-laser h-100 shadow-sm">
                <div class="card-body p-3 p-xl-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="text-muted small text-uppercase fw-semibold" style="letter-spacing: 0.05em;">
                                Sessions &amp; Clients
                            </span>
                            <div class="rounded-circle p-2" style="background-color: #f0f9ff; color: #0284c7;">
                                <i class="bi bi-people fs-5"></i>
                            </div>
                        </div>
                        <h2 class="fw-bold mb-1 text-dark" style="letter-spacing: -0.02em;">
                            {{ $periodSessionsCount }} <span class="fs-6 fw-normal text-muted">Sessions</span>
                        </h2>
                    </div>
                    <div class="mt-3 pt-2 border-top d-flex justify-content-between align-items-center small text-muted">
                        <span>{{ $totalPatientsCount }} Registered Clients</span>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">
                            {{ $todaySessionsCount }} Today
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Financial Health Highlights Bar --}}
    <div class="card border-0 shadow-sm rounded-3 bg-white mb-4 p-3">
        <div class="row align-items-center g-3">
            <div class="col-12 col-md-3 border-end-md">
                <div class="d-flex align-items-center gap-2">
                    <div class="p-2 rounded bg-light text-primary">
                        <i class="bi bi-tag fs-5"></i>
                    </div>
                    <div>
                        <span class="text-muted small d-block">Period Discounts Given</span>
                        <span class="fw-bold text-dark">PKR {{ number_format($periodDiscounts, 2) }}</span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-3 border-end-md">
                <div class="d-flex align-items-center gap-2">
                    <div class="p-2 rounded bg-light text-warning">
                        <i class="bi bi-hourglass-split fs-5"></i>
                    </div>
                    <div>
                        <span class="text-muted small d-block">Cumulative Patient Dues</span>
                        <span class="fw-bold {{ $totalOutstandingDue > 0 ? 'text-danger' : 'text-dark' }}">
                            PKR {{ number_format($totalOutstandingDue, 2) }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-3 border-end-md">
                <div class="d-flex align-items-center gap-2">
                    <div class="p-2 rounded bg-light text-success">
                        <i class="bi bi-safe fs-5"></i>
                    </div>
                    <div>
                        <span class="text-muted small d-block">All-Time Cumulative Profit</span>
                        <span class="fw-bold text-success">PKR {{ number_format($lifetimeProfit, 2) }}</span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-3 text-md-end">
                <a href="{{ route('laser.expenses.create') }}" class="btn btn-sm btn-outline-danger me-1">
                    <i class="bi bi-dash-circle me-1"></i> Add Expense
                </a>
                <a href="{{ route('laser.reports.financial') }}" class="btn btn-sm btn-dark">
                    <i class="bi bi-printer me-1"></i> Audit Statement
                </a>
            </div>
        </div>
    </div>

    {{-- Two Column Detail Section --}}
    <div class="row g-4 mb-4">
        {{-- Recent Laser Sessions --}}
        <div class="col-12 col-lg-8">
            <div class="card border shadow-sm rounded-3 h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <div class="p-1 rounded bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-lightning-charge fs-5"></i>
                        </div>
                        <h6 class="mb-0 fw-bold text-dark">Recent Laser Sessions</h6>
                    </div>
                    <a href="{{ route('laser.sessions.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                        View All <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small text-uppercase text-secondary" style="font-size: 0.72rem; letter-spacing: 0.05em;">
                                <tr>
                                    <th class="ps-3">Patient</th>
                                    <th>Procedure</th>
                                    <th>Session</th>
                                    <th>Parameters</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th class="text-end pe-3">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentSessions as $session)
                                    @php
                                        $initial = strtoupper(substr($session->patient->first_name, 0, 1));
                                    @endphp
                                    <tr>
                                        <td class="ps-3">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="laser-avatar flex-shrink-0">
                                                    {{ $initial }}
                                                </div>
                                                <div>
                                                    <a href="{{ route('laser.patients.show', $session->patient) }}"
                                                       class="fw-bold text-dark text-decoration-none d-block lh-sm">
                                                        {{ $session->patient->full_name }}
                                                    </a>
                                                    <span class="text-muted small" style="font-size: 0.75rem;">
                                                        {{ $session->patient->patient_number }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-semibold text-dark d-block lh-sm">{{ $session->treatment->name ?? 'Custom' }}</span>
                                            <span class="text-muted small" style="font-size: 0.75rem;">
                                                <i class="bi bi-calendar-event me-1"></i>{{ $session->session_date->format('d M Y') }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border rounded-pill px-2">
                                                #{{ $session->session_number }}{{ $session->total_sessions ? ' / ' . $session->total_sessions : '' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="small lh-sm">
                                                @if ($session->fluence)
                                                    <span class="badge bg-info-subtle text-info border border-info-subtle" style="font-size: 0.7rem;">{{ $session->fluence }}</span>
                                                @endif
                                                @if ($session->pulses_count)
                                                    <div class="text-muted" style="font-size: 0.7rem;">{{ number_format($session->pulses_count) }} shots</div>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark">PKR {{ number_format($session->total_amount, 2) }}</div>
                                            @if ($session->discount > 0)
                                                <div class="text-danger small" style="font-size: 0.7rem;">&minus;PKR {{ number_format($session->discount, 0) }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $session->statusColor() }}-subtle text-{{ $session->statusColor() }} border border-{{ $session->statusColor() }}-subtle rounded-pill">
                                                {{ $session->statusLabel() }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-3">
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('laser.sessions.print', $session) }}" class="btn btn-outline-dark" title="Print 80mm Receipt">
                                                    <i class="bi bi-printer"></i>
                                                </a>
                                                <a href="{{ route('laser.sessions.show', $session) }}" class="btn btn-outline-primary" title="Details">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="bi bi-lightning-charge fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                            No laser sessions recorded yet.
                                            <div class="mt-2">
                                                <a href="{{ route('laser.sessions.create') }}" class="btn btn-sm btn-primary">
                                                    <i class="bi bi-plus-circle me-1"></i> Record First Laser Session
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Top Procedures & Expense Distribution --}}
        <div class="col-12 col-lg-4">
            {{-- Top Procedures --}}
            <div class="card border shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-trophy me-1 text-warning"></i> Top Procedures ({{ $periodLabel }})</h6>
                    <span class="badge bg-light text-muted border">Ranked</span>
                </div>
                <div class="card-body p-3">
                    @forelse ($topProcedures as $proc)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center small mb-1">
                                <span class="fw-semibold text-dark text-truncate" style="max-width: 65%;">{{ $proc->name }}</span>
                                <span class="text-success fw-bold">PKR {{ number_format($proc->revenue, 0) }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center text-muted" style="font-size: 0.75rem;">
                                <span>{{ $proc->count }} session{{ $proc->count === 1 ? '' : 's' }}</span>
                                <span>Area: {{ $proc->body_area ?? 'General' }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted small text-center py-3 mb-0">No procedure records in this period.</p>
                    @endforelse
                </div>
            </div>

            {{-- Expense Cost Centers --}}
            <div class="card border shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-pie-chart me-1 text-danger"></i> Cost Centers ({{ $periodLabel }})</h6>
                    <a href="{{ route('laser.expenses.index') }}" class="btn btn-sm btn-link text-decoration-none p-0">Details</a>
                </div>
                <div class="card-body p-3">
                    @forelse ($topExpenseCategories as $cat)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center small mb-1">
                                <span class="fw-medium text-dark text-truncate" style="max-width: 65%;">{{ $cat->name }}</span>
                                <span class="text-danger fw-bold">PKR {{ number_format($cat->total, 0) }}</span>
                            </div>
                            @php
                                $percent = $periodExpenses > 0 ? round(($cat->total / $periodExpenses) * 100, 1) : 0;
                            @endphp
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $percent }}%;" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="text-end text-muted mt-1" style="font-size: 0.7rem;">{{ $percent }}% of expenses</div>
                        </div>
                    @empty
                        <p class="text-muted small text-center py-3 mb-0">No expenses recorded for this period.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
