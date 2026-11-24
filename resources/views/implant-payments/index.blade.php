@extends('layouts.app')

@section('title', 'Dental Implant Payments Ledger')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h4 class="mb-0 fw-bold text-dark">
                <i class="bi bi-journal-check text-success me-2"></i>Dental Implant Payments Ledger
            </h4>
            <p class="text-muted small mb-0">Record of payments received from doctors for supplied dental implants.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('implant-sales.outstanding') }}" class="btn btn-outline-danger">
                <i class="bi bi-exclamation-circle me-1"></i> Doctor Outstandings
            </a>
            <a href="{{ route('implant-sales.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-cart-check me-1"></i> All Sales
            </a>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 border-start border-success border-4 h-100">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold">Total Payments Collected</span>
                    <h3 class="fw-bold mb-0 text-success">PKR {{ number_format($stats['total_amount'], 2) }}</h3>
                    <span class="small text-muted">{{ $stats['total_payments'] }} payment receipts issued</span>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm border-0 border-start border-info border-4 h-100">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold">Receiving Clinic</span>
                    <h4 class="fw-bold mb-0 text-dark">{{ \App\Models\Setting::get('clinic_name', 'Dr Badshah Afsar') }}</h4>
                    <span class="small text-muted">Implants &amp; Maxillofacial Supplies</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Search Bar --}}
    <div class="card shadow-sm mb-3">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('implant-payments.index') }}" class="row g-2">
                <div class="col-md-10">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control"
                            placeholder="Search by receipt #, sale invoice #, doctor name, reference #..."
                            value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-primary flex-grow-1">Search</button>
                    @if(request('search'))
                        <a href="{{ route('implant-payments.index') }}" class="btn btn-sm btn-outline-secondary" title="Clear">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Payments Table --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Receipt #</th>
                            <th>Date</th>
                            <th>Sale Invoice #</th>
                            <th>Doctor / Buyer</th>
                            <th>Payment Method</th>
                            <th>Amount (PKR)</th>
                            <th>Reference #</th>
                            <th>Recorded By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payments as $pay)
                            <tr>
                                <td class="ps-3 fw-bold text-primary">{{ $pay->payment_number }}</td>
                                <td>{{ $pay->payment_date->format('d M Y') }}</td>
                                <td>
                                    <a href="{{ route('implant-sales.show', $pay->sale) }}" class="fw-semibold text-dark text-decoration-none">
                                        {{ $pay->sale->sale_number }}
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ route('implant-buyers.show', $pay->buyer) }}" class="fw-semibold text-dark text-decoration-none">
                                        {{ $pay->buyer->displayName() }}
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border text-uppercase">{{ str_replace('_', ' ', $pay->payment_method) }}</span>
                                </td>
                                <td>
                                    <span class="fw-bold text-success fs-6">PKR {{ number_format($pay->amount, 2) }}</span>
                                </td>
                                <td>{{ $pay->reference_number ?: '—' }}</td>
                                <td class="small text-muted">{{ $pay->createdBy->name ?? 'System' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-journal-x fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                    No implant payments logged in the ledger yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($payments->hasPages())
                <div class="p-3 border-top">
                    {{ $payments->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
