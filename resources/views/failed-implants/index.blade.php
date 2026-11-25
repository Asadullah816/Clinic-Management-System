@extends('layouts.app')

@section('title', 'Failed Dental Implants (Separately Calculated)')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h4 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-x-octagon text-danger me-2"></i>Failed Dental Implants
                </h4>
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                    Calculated Separately
                </span>
            </div>
            <p class="text-muted small mb-0">
                Isolated tracking of clinical complications, osseointegration failures, contamination, fractures, and warranty claims.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('failed-implants.create') }}" class="btn btn-danger">
                <i class="bi bi-plus-lg me-1"></i> Report Failed Implant
            </a>
            <a href="{{ route('implants.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-box-seam me-1"></i> Implants Inventory
            </a>
            <a href="{{ route('implant-sales.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-receipt me-1"></i> Sales Invoices
            </a>
        </div>
    </div>

    {{-- Highlight Notice: Separate Accounting --}}
    <div class="alert alert-info border-0 shadow-sm d-flex align-items-center py-2 px-3 mb-4" role="alert">
        <i class="bi bi-info-circle-fill fs-5 text-info me-2"></i>
        <div class="small">
            <strong>Separate Ledger Isolation:</strong> Failed implant losses and replacement values are tracked separately. They do not dilute active sales revenue or regular inventory balances.
        </div>
    </div>

    {{-- Financial & Clinical Breakdown Cards (Calculated Separately) --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 border-start border-danger border-4 h-100">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold">Total Failed Units</span>
                    <h3 class="fw-bold mb-0 text-danger">{{ number_format($stats['total_failed_units']) }}</h3>
                    <span class="small text-muted">Documented complications</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 border-start border-dark border-4 h-100">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold">Total Failure Cost (Loss)</span>
                    <h3 class="fw-bold mb-0 text-dark">PKR {{ number_format($stats['total_failed_cost'], 2) }}</h3>
                    <span class="small text-muted">Separate from sales turnover</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 border-start border-success border-4 h-100">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold">Vendor Replaced</span>
                    <h3 class="fw-bold mb-0 text-success">{{ number_format($stats['vendor_replaced_units']) }} units</h3>
                    <span class="small text-muted">PKR {{ number_format($stats['vendor_replaced_cost'], 2) }} covered</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 border-start border-warning border-4 h-100">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold">Pending Warranty Claims</span>
                    <h3 class="fw-bold mb-0 text-warning">{{ $stats['pending_warranty_count'] }}</h3>
                    <span class="small text-muted">Net loss: PKR {{ number_format($stats['clinic_loss_cost'], 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter & Search Bar --}}
    <div class="card shadow-sm mb-3">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('failed-implants.index') }}" class="row g-2 align-items-center">
                <div class="col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control"
                            placeholder="Failure #, patient, tooth, doctor..."
                            value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <select name="source" class="form-select form-select-sm">
                        <option value="">All Sources</option>
                        <option value="stock" {{ request('source') === 'stock' ? 'selected' : '' }}>Direct Inventory / Stock</option>
                        <option value="sale" {{ request('source') === 'sale' ? 'selected' : '' }}>Sold to Doctor</option>
                        <option value="internal" {{ request('source') === 'internal' ? 'selected' : '' }}>In-House Procedure</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="reason" class="form-select form-select-sm">
                        <option value="">All Failure Reasons</option>
                        @foreach (\App\Models\FailedImplant::reasons() as $key => $label)
                            <option value="{{ $key }}" {{ request('reason') === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="warranty_status" class="form-select form-select-sm">
                        <option value="">All Warranty Statuses</option>
                        @foreach (\App\Models\FailedImplant::warrantyStatuses() as $key => $label)
                            <option value="{{ $key }}" {{ request('warranty_status') === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="buyer_id" class="form-select form-select-sm">
                        <option value="">All Doctors / Clinics</option>
                        @foreach ($buyers as $buyer)
                            <option value="{{ $buyer->id }}" {{ request('buyer_id') == $buyer->id ? 'selected' : '' }}>
                                {{ $buyer->doctor_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-primary w-100">
                        <i class="bi bi-filter"></i>
                    </button>
                    @if (request()->hasAny(['search', 'source', 'reason', 'warranty_status', 'buyer_id']))
                        <a href="{{ route('failed-implants.index') }}" class="btn btn-sm btn-outline-secondary" title="Reset Filters">
                            <i class="bi bi-x"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Main Table --}}
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Failure #</th>
                            <th>Date</th>
                            <th>Implant Model</th>
                            <th>Source / Doctor</th>
                            <th>Tooth &amp; Patient</th>
                            <th>Stage &amp; Reason</th>
                            <th class="text-end">Cost / Loss (PKR)</th>
                            <th>Warranty Resolution</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($failedImplants as $failure)
                            <tr>
                                <td class="ps-3 fw-bold">
                                    <a href="{{ route('failed-implants.show', $failure) }}" class="text-danger text-decoration-none">
                                        {{ $failure->failure_number }}
                                    </a>
                                </td>
                                <td>{{ $failure->failure_date->format('d M Y') }}</td>
                                <td>
                                    @if ($failure->implant)
                                        <div class="fw-semibold text-dark">{{ $failure->implant->name }}</div>
                                        <div class="text-muted small">
                                            {{ $failure->implant->brand }} &bull; Ø {{ $failure->implant->diameter }} × {{ $failure->implant->length }}
                                        </div>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($failure->source === 'sale' && $failure->buyer)
                                        <a href="{{ route('implant-buyers.show', $failure->buyer) }}" class="text-decoration-none fw-semibold">
                                            {{ $failure->buyer->doctor_name }}
                                        </a>
                                        @if ($failure->sale)
                                            <div class="small">
                                                <a href="{{ route('implant-sales.show', $failure->sale) }}" class="text-muted text-decoration-none">
                                                    Inv: {{ $failure->sale->sale_number }}
                                                </a>
                                            </div>
                                        @endif
                                    @elseif ($failure->source === 'stock')
                                        <span class="badge bg-secondary-subtle text-secondary border">Direct Inventory / Stock</span>
                                    @else
                                        <span class="badge bg-primary-subtle text-primary border">In-House Surgery</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($failure->tooth_number)
                                        <span class="badge bg-light text-dark border me-1">Tooth #{{ $failure->tooth_number }}</span>
                                    @endif
                                    <span class="small text-muted">{{ $failure->patient_name ?: '—' }}</span>
                                </td>
                                <td>
                                    <div class="small fw-semibold text-dark">{{ $failure->reasonLabel() }}</div>
                                    <div class="text-muted small">{{ $failure->stageLabel() }}</div>
                                </td>
                                <td class="text-end">
                                    <div class="fw-bold text-danger">PKR {{ number_format($failure->total_cost, 2) }}</div>
                                    <div class="text-muted small">{{ $failure->quantity }}x @ PKR {{ number_format($failure->unit_cost, 2) }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $failure->warrantyColor() }}-subtle text-{{ $failure->warrantyColor() }} border border-{{ $failure->warrantyColor() }}-subtle">
                                        {{ $failure->warrantyLabel() }}
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('failed-implants.show', $failure) }}" class="btn btn-sm btn-outline-secondary me-1" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('failed-implants.edit', $failure) }}" class="btn btn-sm btn-outline-primary me-1" title="Edit / Update Resolution">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @if (auth()->user()->isAdmin())
                                        <form method="POST" action="{{ route('failed-implants.destroy', $failure) }}" class="d-inline"
                                            onsubmit="return confirm('Are you sure you want to delete this failure record? If it was logged from stock, inventory will be restored.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Record">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="bi bi-check2-circle text-success fs-1 d-block mb-2"></i>
                                    <strong>No Failed Implants Recorded</strong>
                                    <p class="small text-muted mb-3">All dental implants are currently performing without documented clinical or packaging complications.</p>
                                    <a href="{{ route('failed-implants.create') }}" class="btn btn-sm btn-danger">
                                        <i class="bi bi-plus-lg me-1"></i> Log New Failure Record
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($failedImplants->hasPages())
                <div class="p-3 border-top">
                    {{ $failedImplants->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
