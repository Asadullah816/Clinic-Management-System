@extends('layouts.app')

@section('title', 'Laser Shared Investment Financial Statement')

@section('content')
    <style>
        .statement-canvas {
            max-width: 960px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 1rem;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.08), 0 8px 10px -6px rgba(15, 23, 42, 0.04);
            border: 1px solid #e2e8f0;
        }

        .metric-tile {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            padding: 1.1rem;
            transition: all 0.2s ease;
        }

        .metric-tile:hover {
            background: #ffffff;
            border-color: #cbd5e1;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05);
        }

        .hero-profit-card {
            background: linear-gradient(135deg, #091e42 0%, #0c2d6b 40%, #0369a1 100%);
            border-radius: 0.875rem;
            color: #ffffff;
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 20px -4px rgba(2, 132, 199, 0.3);
        }

        .hero-profit-card::after {
            content: '';
            position: absolute;
            right: -10%;
            top: -30%;
            width: 250px;
            height: 250px;
            background: radial-gradient(circle, rgba(56, 189, 248, 0.25) 0%, rgba(2, 132, 199, 0) 70%);
            border-radius: 50%;
        }

        .date-chip {
            padding: 0.25rem 0.65rem;
            border-radius: 50rem;
            font-size: 0.75rem;
            font-weight: 500;
            background: #f1f5f9;
            color: #475569;
            text-decoration: none;
            border: 1px solid #e2e8f0;
        }

        .date-chip:hover {
            background: #e2e8f0;
            color: #0f172a;
        }

        @media print {
            .no-print, .sidebar, .app-navbar, .offcanvas, .btn {
                display: none !important;
            }
            body {
                background: #ffffff !important;
                margin: 0 !important;
                padding: 0 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .statement-canvas {
                max-width: 100% !important;
                width: 100% !important;
                border: none !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                padding: 0 !important;
            }
        }
    </style>

    {{-- Screen Action Toolbar --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3 no-print">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h4 class="mb-0 fw-bold" style="color: #0f172a;">Laser Financial &amp; Partner Audit</h4>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                    <i class="bi bi-shield-lock me-1"></i> Segregated Ledger
                </span>
            </div>
            <p class="text-muted small mb-0 mt-1">Audit-ready statement calculating net distributable profits for equipment partners.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-dark d-inline-flex align-items-center gap-1 shadow-sm px-3" onclick="window.print()">
                <i class="bi bi-printer"></i> Print Audit Statement
            </button>
            <a href="{{ route('laser.dashboard') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
                <i class="bi bi-arrow-left"></i> Laser Dashboard
            </a>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4 no-print bg-white">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('laser.reports.financial') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-4">
                    <label class="form-label small text-muted mb-0 fw-semibold">From Date</label>
                    <input type="date" name="from" class="form-control form-control-sm" value="{{ $from }}">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label small text-muted mb-0 fw-semibold">To Date</label>
                    <input type="date" name="to" class="form-control form-control-sm" value="{{ $to }}">
                </div>
                <div class="col-12 col-md-4 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-sm btn-primary w-100 fw-semibold">
                        <i class="bi bi-funnel me-1"></i> Apply Filter
                    </button>
                    @if ($from || $to)
                        <a href="{{ route('laser.reports.financial') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                    @endif
                </div>
            </form>
            <div class="d-flex flex-wrap align-items-center gap-1 mt-2 pt-2 border-top">
                <span class="text-muted small me-2" style="font-size: 0.75rem;">Presets:</span>
                <a href="{{ route('laser.reports.financial', ['from' => today()->format('Y-m-d'), 'to' => today()->format('Y-m-d')]) }}" class="date-chip">Today</a>
                <a href="{{ route('laser.reports.financial', ['from' => now()->startOfWeek()->format('Y-m-d'), 'to' => now()->endOfWeek()->format('Y-m-d')]) }}" class="date-chip">This Week</a>
                <a href="{{ route('laser.reports.financial', ['from' => now()->startOfMonth()->format('Y-m-d'), 'to' => now()->endOfMonth()->format('Y-m-d')]) }}" class="date-chip">This Month</a>
                <a href="{{ route('laser.reports.financial', ['from' => now()->startOfYear()->format('Y-m-d'), 'to' => now()->endOfYear()->format('Y-m-d')]) }}" class="date-chip">This Year</a>
                <a href="{{ route('laser.reports.financial') }}" class="date-chip">All Time</a>
            </div>
        </div>
    </div>

    {{-- Statement Document Sheet --}}
    <div class="statement-canvas p-4 p-md-5">
        {{-- Statement Letterhead --}}
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start pb-4 mb-4 border-bottom">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <div class="rounded-3 d-flex align-items-center justify-content-center text-white fw-bold shadow-sm"
                         style="width: 40px; height: 40px; background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);">
                        <i class="bi bi-lightning-charge-fill fs-5"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0 text-dark" style="letter-spacing: -0.01em;">
                            {{ \App\Models\Setting::get('clinic_name', config('app.name', 'Mayar Skin Care & Aesthetic Clinic')) }}
                        </h4>
                        <div class="text-muted small">Laser Dermatology &bull; Shared Equipment Asset Unit</div>
                    </div>
                </div>
                <div class="text-muted small ps-1 mt-2">
                    @if ($address = \App\Models\Setting::get('address'))
                        <div><i class="bi bi-geo-alt me-1"></i> {{ $address }}</div>
                    @endif
                    <div><i class="bi bi-telephone me-1"></i> {{ \App\Models\Setting::get('phone', '03489030035') }}</div>
                </div>
            </div>

            <div class="text-sm-end mt-3 mt-sm-0">
                <span class="badge bg-dark text-white text-uppercase px-3 py-1 mb-2" style="letter-spacing: 0.05em; font-size: 0.72rem;">
                    Financial Audit Statement
                </span>
                <div class="small fw-bold text-dark">
                    @if ($from && $to)
                        {{ \Carbon\Carbon::parse($from)->format('d M Y') }} &mdash; {{ \Carbon\Carbon::parse($to)->format('d M Y') }}
                    @elseif($from)
                        From {{ \Carbon\Carbon::parse($from)->format('d M Y') }} onwards
                    @elseif($to)
                        Up to {{ \Carbon\Carbon::parse($to)->format('d M Y') }}
                    @else
                        Cumulative All-Time Statement
                    @endif
                </div>
                <div class="text-muted small" style="font-size: 0.75rem;">
                    Audit ID: <code>LASER-{{ now()->format('Ymd-His') }}</code><br>
                    Generated: {{ now()->format('d M Y, h:i A') }}
                </div>
            </div>
        </div>

        {{-- Hero Distributable Operating Profit Card --}}
        <div class="hero-profit-card p-4 mb-4">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-2">
                <span class="text-uppercase small fw-semibold" style="letter-spacing: 0.08em; opacity: 0.9;">
                    Net Distributable Shared Profit
                </span>
                <span class="badge bg-white bg-opacity-25 rounded-pill px-3 py-1 mt-2 mt-sm-0 small">
                    <i class="bi bi-check-circle me-1"></i> {{ $profitMargin }}% Net Operating Return
                </span>
            </div>

            <h1 class="display-5 fw-bold mb-2 text-white" style="letter-spacing: -0.02em;">
                PKR {{ number_format($netProfit, 2) }}
            </h1>

            <div class="pt-2 border-top border-white border-opacity-20 d-flex flex-wrap justify-content-between align-items-center small" style="opacity: 0.9;">
                <div>
                    <strong>Accounting Formula:</strong> Collected Inflow (PKR {{ number_format($totalRevenue, 2) }}) &minus; Documented Expenses (PKR {{ number_format($totalExpenses, 2) }})
                </div>
                <div class="text-white-50">
                    {{ $sessionsCount }} Sessions &bull; {{ $expensesCount }} Expense Vouchers
                </div>
            </div>
        </div>

        {{-- 4 Core Audit Metric Tiles --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="metric-tile h-100">
                    <span class="text-muted small text-uppercase fw-semibold d-block mb-1" style="font-size: 0.72rem; letter-spacing: 0.05em;">
                        1. Collected Cash Revenue
                    </span>
                    <h4 class="fw-bold text-success mb-1">PKR {{ number_format($totalRevenue, 2) }}</h4>
                    <span class="text-muted small" style="font-size: 0.75rem;">Realized cash inflow</span>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="metric-tile h-100">
                    <span class="text-muted small text-uppercase fw-semibold d-block mb-1" style="font-size: 0.72rem; letter-spacing: 0.05em;">
                        2. Laser Operating Expenses
                    </span>
                    <h4 class="fw-bold text-danger mb-1">PKR {{ number_format($totalExpenses, 2) }}</h4>
                    <span class="text-muted small" style="font-size: 0.75rem;">Parts, servicing, consumables</span>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="metric-tile h-100">
                    <span class="text-muted small text-uppercase fw-semibold d-block mb-1" style="font-size: 0.72rem; letter-spacing: 0.05em;">
                        3. Laser Discounts Granted
                    </span>
                    <h4 class="fw-bold text-secondary mb-1">&minus; PKR {{ number_format($totalDiscounts, 2) }}</h4>
                    <span class="text-muted small" style="font-size: 0.75rem;">Separate laser discounts</span>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="metric-tile h-100">
                    <span class="text-muted small text-uppercase fw-semibold d-block mb-1" style="font-size: 0.72rem; letter-spacing: 0.05em;">
                        4. Pending Client Balances
                    </span>
                    <h4 class="fw-bold {{ $totalOutstanding > 0 ? 'text-warning-emphasis' : 'text-dark' }} mb-1">
                        PKR {{ number_format($totalOutstanding, 2) }}
                    </h4>
                    <span class="text-muted small" style="font-size: 0.75rem;">Uncollected session dues</span>
                </div>
            </div>
        </div>

        {{-- Breakdown Tables Section --}}
        <div class="row g-4 mb-4">
            {{-- Cost Center Expense Breakdown --}}
            <div class="col-12 col-md-6">
                <div class="card border rounded-3 h-100 shadow-none">
                    <div class="card-header bg-light py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-dark small text-uppercase" style="letter-spacing: 0.04em;">
                            <i class="bi bi-wallet2 text-danger me-1"></i> Documented Expenses
                        </span>
                        <span class="badge bg-danger text-white rounded-pill">Cost Centers</span>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0 align-middle">
                            <thead class="table-light small text-secondary" style="font-size: 0.72rem;">
                                <tr>
                                    <th class="ps-3">Category</th>
                                    <th class="text-end">Share</th>
                                    <th class="text-end pe-3">Amount (PKR)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($expensesByCategory as $cat)
                                    @php
                                        $percent = $totalExpenses > 0 ? round(($cat->total / $totalExpenses) * 100, 1) : 0;
                                    @endphp
                                    <tr>
                                        <td class="ps-3 fw-medium text-dark">{{ $cat->name }}</td>
                                        <td class="text-end text-muted small">{{ $percent }}%</td>
                                        <td class="text-end pe-3 fw-bold text-danger">PKR {{ number_format($cat->total, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-3 text-muted small">No expenses logged in this period.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td class="ps-3">Total Operating Expenses</td>
                                    <td class="text-end text-muted small">100%</td>
                                    <td class="text-end pe-3 text-danger">PKR {{ number_format($totalExpenses, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Revenue Collections by Channel --}}
            <div class="col-12 col-md-6">
                <div class="card border rounded-3 h-100 shadow-none">
                    <div class="card-header bg-light py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-dark small text-uppercase" style="letter-spacing: 0.04em;">
                            <i class="bi bi-cash-stack text-success me-1"></i> Realized Inflows
                        </span>
                        <span class="badge bg-success text-white rounded-pill">Payment Modes</span>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0 align-middle">
                            <thead class="table-light small text-secondary" style="font-size: 0.72rem;">
                                <tr>
                                    <th class="ps-3">Payment Method</th>
                                    <th class="text-end">Share</th>
                                    <th class="text-end pe-3">Amount (PKR)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($revenueByMethod as $method)
                                    @php
                                        $percent = $totalRevenue > 0 ? round(($method->total / $totalRevenue) * 100, 1) : 0;
                                    @endphp
                                    <tr>
                                        <td class="ps-3 fw-medium text-dark text-capitalize">{{ $method->payment_method }}</td>
                                        <td class="text-end text-muted small">{{ $percent }}%</td>
                                        <td class="text-end pe-3 fw-bold text-success">PKR {{ number_format($method->total, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-3 text-muted small">No payments recorded in this period.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td class="ps-3">Total Cash Collected</td>
                                    <td class="text-end text-muted small">100%</td>
                                    <td class="text-end pe-3 text-success">PKR {{ number_format($totalRevenue, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Segregation & Verification Certificate Notice --}}
        <div class="p-3 bg-light rounded-3 border mb-4">
            <div class="d-flex align-items-start gap-2">
                <i class="bi bi-shield-check text-primary fs-5 mt-1"></i>
                <div class="small text-secondary">
                    <strong class="text-dark d-block mb-1">Partner Shared Investment Statement Guarantee</strong>
                    This audit document presents exclusively the direct collections and direct operating costs of the shared Laser Unit equipment. In accordance with the partnership terms, regular clinic treatments, general staff payroll, and non-laser facility overheads are strictly excluded from this distribution calculation.
                </div>
            </div>
        </div>

        {{-- Verification Sign-Off Blocks --}}
        <div class="row pt-5 mt-4">
            <div class="col-6">
                <div class="border-top pt-2 small text-muted" style="width: 240px;">
                    <div class="fw-bold text-dark">Partner / Investor Representative</div>
                    <div style="font-size: 0.75rem;">Verified for Profit Distribution</div>
                </div>
            </div>
            <div class="col-6 text-end">
                <div class="border-top pt-2 small text-muted d-inline-block text-start" style="width: 240px;">
                    <div class="fw-bold text-dark">Managing Medical Director</div>
                    <div style="font-size: 0.75rem;">Clinic Management System Certified</div>
                </div>
            </div>
        </div>

        <div class="text-center text-muted small mt-4 pt-2 border-top" style="font-size: 0.7rem;">
            Laser Aesthetics Shared Asset Statement &bull; Generated from secure clinic database on {{ now()->format('d M Y, h:i A') }}
        </div>
    </div>
@endsection
