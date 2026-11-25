@extends('layouts.app')

@section('title', $failedImplant->failure_number . ' - Failed Dental Implant Record')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h4 class="mb-0 fw-bold text-dark">{{ $failedImplant->failure_number }}</h4>
                <span class="badge bg-{{ $failedImplant->warrantyColor() }}-subtle text-{{ $failedImplant->warrantyColor() }} border border-{{ $failedImplant->warrantyColor() }}-subtle px-2 py-1">
                    {{ $failedImplant->warrantyLabel() }}
                </span>
                <span class="badge bg-secondary-subtle text-secondary border px-2 py-1">
                    Calculated Separately
                </span>
            </div>
            <p class="text-muted small mb-0">
                Logged on {{ $failedImplant->failure_date->format('l, d M Y') }}
                &bull; Recorded by <strong class="text-dark">{{ $failedImplant->createdBy->name ?? 'System' }}</strong>
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('failed-implants.edit', $failedImplant) }}" class="btn btn-outline-primary">
                <i class="bi bi-pencil me-1"></i> Edit / Update Warranty
            </a>
            <a href="{{ route('failed-implants.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> All Failed Implants
            </a>
        </div>
    </div>

    {{-- Details Card --}}
    <div class="row g-3 mb-4">
        <div class="col-md-8">
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-x-octagon text-danger me-2"></i>Clinical Complication &amp; Implant Details
                    </h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered mb-0">
                        <tbody>
                            <tr>
                                <th class="w-25 bg-light">Implant Fixture</th>
                                <td class="fw-bold text-primary">
                                    @if ($failedImplant->implant)
                                        <a href="{{ route('implants.show', $failedImplant->implant) }}" class="text-decoration-none">
                                            {{ $failedImplant->implant->fullSpecification() }}
                                        </a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <th class="w-25 bg-light">Lot / Batch Number</th>
                                <td>{{ $failedImplant->implant?->lot_number ?: '—' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Failure Origin</th>
                                <td>
                                    @if ($failedImplant->source === 'sale')
                                        <span class="badge bg-info-subtle text-info border">Sold Invoice to Doctor</span>
                                    @elseif ($failedImplant->source === 'stock')
                                        <span class="badge bg-secondary-subtle text-secondary border">Direct Inventory / Stock</span>
                                    @else
                                        <span class="badge bg-primary-subtle text-primary border">In-House Procedure</span>
                                    @endif
                                </td>
                                <th class="bg-light">Doctor / Buyer</th>
                                <td>
                                    @if ($failedImplant->buyer)
                                        <a href="{{ route('implant-buyers.show', $failedImplant->buyer) }}" class="text-decoration-none fw-semibold">
                                            {{ $failedImplant->buyer->displayName() }}
                                        </a>
                                    @else
                                        <span class="text-muted">Direct Clinic Stock</span>
                                    @endif
                                </td>
                            </tr>
                            @if ($failedImplant->sale)
                                <tr>
                                    <th class="bg-light">Sale Invoice</th>
                                    <td colspan="3">
                                        <a href="{{ route('implant-sales.show', $failedImplant->sale) }}" class="text-decoration-none fw-bold">
                                            {{ $failedImplant->sale->sale_number }}
                                        </a>
                                        <span class="text-muted small ms-2">({{ $failedImplant->sale->sale_date->format('d M Y') }})</span>
                                    </td>
                                </tr>
                            @endif
                            <tr>
                                <th class="bg-light">Failure Stage</th>
                                <td class="fw-semibold">{{ $failedImplant->stageLabel() }}</td>
                                <th class="bg-light">Failure Reason</th>
                                <td class="fw-semibold text-danger">{{ $failedImplant->reasonLabel() }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Tooth Number / Site</th>
                                <td>{{ $failedImplant->tooth_number ? 'Tooth #' . $failedImplant->tooth_number : '—' }}</td>
                                <th class="bg-light">Patient Name</th>
                                <td>{{ $failedImplant->patient_name ?: '—' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Replacement Implant</th>
                                <td colspan="3">
                                    @if ($failedImplant->replacementImplant)
                                        <span class="badge bg-success-subtle text-success border">
                                            {{ $failedImplant->replacementImplant->fullSpecification() }}
                                        </span>
                                    @else
                                        <span class="text-muted">None specified</span>
                                    @endif
                                </td>
                            </tr>
                            @if ($failedImplant->notes)
                                <tr>
                                    <th class="bg-light">Notes / Observations</th>
                                    <td colspan="3">{{ $failedImplant->notes }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Financial Isolation Card --}}
        <div class="col-md-4">
            <div class="card shadow-sm border-0 border-top border-danger border-4 h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-cash-stack text-danger me-2"></i>Isolated Financial Impact
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Quantity Failed:</span>
                        <span class="fw-bold">{{ $failedImplant->quantity }} unit(s)</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Unit Cost / Valuation:</span>
                        <span>PKR {{ number_format($failedImplant->unit_cost, 2) }}</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="fw-bold text-dark">Total Loss / Cost:</span>
                        <span class="fw-bold text-danger fs-5">PKR {{ number_format($failedImplant->total_cost, 2) }}</span>
                    </div>

                    <div class="p-3 bg-light rounded-2 border">
                        <span class="small text-muted d-block fw-semibold mb-1">Accounting Status</span>
                        <span class="badge bg-{{ $failedImplant->warrantyColor() }}-subtle text-{{ $failedImplant->warrantyColor() }} border border-{{ $failedImplant->warrantyColor() }}-subtle px-2 py-1 mb-2">
                            {{ $failedImplant->warrantyLabel() }}
                        </span>
                        <p class="small text-muted mb-0">
                            This amount is calculated separately and isolated from regular doctor receivable balances and sales revenue.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
