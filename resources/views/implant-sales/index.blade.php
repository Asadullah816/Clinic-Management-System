@extends('layouts.app')

@section('title', 'Dental Implant Sales')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h4 class="mb-0 fw-bold text-dark">
                <i class="bi bi-cart-check text-primary me-2"></i>Dental Implant Sales
            </h4>
            <p class="text-muted small mb-0">Track implant dispatches to doctors, in-house surgeries, invoices, and payment statuses.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('implant-sales.outstanding') }}" class="btn btn-outline-danger">
                <i class="bi bi-exclamation-circle me-1"></i> Doctor Outstandings (PKR {{ number_format($stats['total_due'], 2) }})
            </a>
            <a href="{{ route('implant-sales.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> New Implant Sale
            </a>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 border-start border-primary border-4 h-100">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold">Total Invoices</span>
                    <h3 class="fw-bold mb-0 text-dark">{{ $stats['total_sales'] }}</h3>
                    <span class="small text-muted">Sales recorded</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 border-start border-info border-4 h-100">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold">Total Gross Value</span>
                    <h3 class="fw-bold mb-0 text-info">PKR {{ number_format($stats['total_amount'], 2) }}</h3>
                    <span class="small text-muted">Implants billed</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 border-start border-success border-4 h-100">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold">Collected Payments</span>
                    <h3 class="fw-bold mb-0 text-success">PKR {{ number_format($stats['total_paid'], 2) }}</h3>
                    <span class="small text-muted">Cleared revenue</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 border-start border-danger border-4 h-100">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold">Doctor Outstandings</span>
                    <h3 class="fw-bold mb-0 {{ $stats['total_due'] > 0 ? 'text-danger' : 'text-muted' }}">
                        PKR {{ number_format($stats['total_due'], 2) }}
                    </h3>
                    <span class="small text-muted">Pending balance</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter & Search --}}
    <div class="card shadow-sm mb-3">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('implant-sales.index') }}" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control"
                            placeholder="Search by sale #, doctor, clinic, patient ref..."
                            value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="buyer_id" class="form-select form-select-sm">
                        <option value="">-- All Doctors &amp; In-House --</option>
                        @foreach ($buyers as $b)
                            <option value="{{ $b->id }}" {{ request('buyer_id') == $b->id ? 'selected' : '' }}>
                                {{ $b->displayName() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">-- All Statuses --</option>
                        <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Fully Paid</option>
                        <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Partial Due</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending / Unpaid</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-primary flex-grow-1">Filter</button>
                    @if(request('search') || request('buyer_id') || request('status'))
                        <a href="{{ route('implant-sales.index') }}" class="btn btn-sm btn-outline-secondary" title="Clear">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Sales Table --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Sale Invoice #</th>
                            <th>Date</th>
                            <th>Doctor / Buyer</th>
                            <th>Implants Delivered</th>
                            <th>Total (PKR)</th>
                            <th>Paid (PKR)</th>
                            <th>Due (PKR)</th>
                            <th>Status</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sales as $sale)
                            <tr>
                                <td class="ps-3 fw-bold">
                                    <a href="{{ route('implant-sales.show', $sale) }}" class="text-primary text-decoration-none">
                                        {{ $sale->sale_number }}
                                    </a>
                                    @if ($sale->patient_reference)
                                        <div class="small text-muted">Pt: {{ $sale->patient_reference }}</div>
                                    @endif
                                </td>
                                <td>{{ $sale->sale_date->format('d M Y') }}</td>
                                <td>
                                    <a href="{{ route('implant-buyers.show', $sale->buyer) }}" class="fw-semibold text-dark text-decoration-none">
                                        {{ $sale->buyer->displayName() }}
                                    </a>
                                    @if ($sale->buyer->phone)
                                        <div class="small text-muted"><i class="bi bi-telephone me-1"></i>{{ $sale->buyer->phone }}</div>
                                    @endif
                                </td>
                                <td>
                                    @foreach ($sale->items as $item)
                                        <div class="small">
                                            <span class="fw-bold">{{ $item->quantity }}x</span> {{ $item->implant?->fullSpecification() }}
                                        </div>
                                    @endforeach
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">PKR {{ number_format($sale->total_amount, 2) }}</div>
                                    @if ($sale->discount > 0)
                                        <div class="small text-muted">Disc: PKR {{ number_format($sale->discount, 2) }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-success fw-semibold">PKR {{ number_format($sale->paid_amount, 2) }}</span>
                                </td>
                                <td>
                                    @if ($sale->due_amount > 0)
                                        <span class="fw-bold text-danger">PKR {{ number_format($sale->due_amount, 2) }}</span>
                                    @else
                                        <span class="badge bg-success-subtle text-success">Cleared</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $sale->statusColor() }}-subtle text-{{ $sale->statusColor() }}">
                                        {{ $sale->statusLabel() }}
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    {{-- WhatsApp Reminder button if due > 0 --}}
                                    @if ($sale->due_amount > 0 && $sale->whatsappReminderUrl())
                                        <a href="{{ $sale->whatsappReminderUrl() }}" target="_blank"
                                            class="btn btn-sm btn-success me-1" style="background-color: #25D366; border-color: #25D366;"
                                            title="Send WhatsApp Reminder">
                                            <i class="bi bi-whatsapp"></i>
                                        </a>
                                    @endif
                                    <a href="{{ route('implant-sales.show', $sale) }}" class="btn btn-sm btn-outline-secondary me-1" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('implant-sales.print', $sale) }}" target="_blank" class="btn btn-sm btn-outline-dark me-1" title="Print Invoice / Challan">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                    <form method="POST" action="{{ route('implant-sales.destroy', $sale) }}" class="d-inline"
                                        onsubmit="return confirm('Delete sale {{ $sale->sale_number }} and restore inventory stock?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete & Restore Stock">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="bi bi-cart-x fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                    No implant sales recorded yet.
                                    <div class="mt-2">
                                        <a href="{{ route('implant-sales.create') }}" class="btn btn-sm btn-primary">
                                            + Create First Implant Sale
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($sales->hasPages())
                <div class="p-3 border-top">
                    {{ $sales->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
