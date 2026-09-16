@extends('layouts.app')

@section('title', 'Financial Summary')

@section('content')

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0">Financial Summary <span class="text-muted small fw-normal">(all time)</span></h5>
            <button type="button" class="btn btn-outline-dark btn-sm no-print" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print
            </button>
        </div>

        <div class="card-body">

            <div class="row g-3">
                <div class="col-md-3">
                    <div class="card border-start border-success border-4 h-100">
                        <div class="card-body">
                            <div class="text-muted small">Total Revenue <span class="text-lowercase">(payments
                                    received)</span></div>
                            <div class="fs-3 fw-bold text-success">{{ number_format($totalRevenue, 2) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-start border-danger border-4 h-100">
                        <div class="card-body">
                            <div class="text-muted small">Total Expenses</div>
                            <div class="fs-3 fw-bold text-danger">{{ number_format($totalExpenses, 2) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-start border-warning border-4 h-100">
                        <div class="card-body">
                            <div class="text-muted small">Outstanding Amount <span class="text-lowercase">(owed by
                                    patients)</span></div>
                            <div class="fs-3 fw-bold {{ $totalOutstanding > 0 ? 'text-danger' : '' }}">
                                {{ number_format($totalOutstanding, 2) }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div
                        class="card h-100 {{ $netProfit >= 0 ? 'border-start border-success border-4' : 'border-start border-danger border-4' }}">
                        <div class="card-body">
                            <div class="text-muted small">Net Profit <span class="text-lowercase">(revenue &minus;
                                    expenses)</span></div>
                            <div class="fs-3 fw-bold {{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ number_format($netProfit, 2) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="alert alert-light border mt-3 mb-0 small text-muted">
                <i class="bi bi-info-circle me-1"></i>
                Revenue counts money actually received (payments), not invoiced amounts.
                The Outstanding amount is money invoiced but not yet received — collecting it
                increases revenue and profit.
            </div>

            <div class="text-muted small mt-3">
                Generated on {{ now()->format('d M Y, H:i') }} by {{ auth()->user()->name }}
            </div>
        </div>
    </div>

    <style>
        @media print {

            .sidebar,
            .navbar,
            footer,
            .no-print {
                display: none !important;
            }

            .main-wrapper {
                margin-left: 0 !important;
            }

            body {
                background: #fff !important;
            }

            .card {
                border: none !important;
                box-shadow: none !important;
            }
        }
    </style>

@endsection
