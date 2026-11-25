@extends('layouts.app')

@section('title', $implantBuyer->doctor_name . ' - Doctor Account Statement')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h4 class="mb-0 fw-bold text-dark">{{ $implantBuyer->doctor_name }}</h4>
                @if ($implantBuyer->is_internal)
                    <span class="badge bg-primary">IN-HOUSE / ADMIN USE</span>
                @endif
                <span class="badge bg-{{ $implantBuyer->status === 'active' ? 'success' : 'secondary' }}">
                    {{ ucfirst($implantBuyer->status) }}
                </span>
            </div>
            <p class="text-muted small mb-0">
                {{ $implantBuyer->clinic_hospital_name ?: 'Dental Surgeon' }}
                @if ($implantBuyer->phone)
                    &bull; <i class="bi bi-telephone ms-1"></i> {{ $implantBuyer->phone }}
                @endif
            </p>
        </div>
        <div class="d-flex gap-2">
            @php $due = $implantBuyer->totalDue(); @endphp
            @if ($due > 0 && $implantBuyer->whatsappReminderUrl())
                <a href="{{ $implantBuyer->whatsappReminderUrl() }}" target="_blank"
                    class="btn btn-success" style="background-color: #25D366; border-color: #25D366;">
                    <i class="bi bi-whatsapp me-1"></i> Send WhatsApp Reminder
                </a>
            @endif
            <a href="{{ route('failed-implants.create', ['buyer_id' => $implantBuyer->id]) }}" class="btn btn-outline-danger">
                <i class="bi bi-x-octagon me-1"></i> Report Failure
            </a>
            <a href="{{ route('implant-sales.create', ['buyer_id' => $implantBuyer->id]) }}" class="btn btn-primary">
                <i class="bi bi-cart-plus me-1"></i> New Implant Sale
            </a>
            <a href="{{ route('implant-buyers.edit', $implantBuyer) }}" class="btn btn-outline-secondary">
                <i class="bi bi-pencil me-1"></i> Edit
            </a>
            <a href="{{ route('implant-buyers.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> All Doctors
            </a>
        </div>
    </div>

    {{-- Financial Overview Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 border-start border-info border-4 h-100">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold">Total Purchases</span>
                    <h3 class="fw-bold mb-0 text-dark">PKR {{ number_format($implantBuyer->totalPurchases(), 2) }}</h3>
                    <span class="small text-muted">{{ $implantBuyer->sales->count() }} invoices issued</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 border-start border-success border-4 h-100">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold">Total Payments Received</span>
                    <h3 class="fw-bold mb-0 text-success">PKR {{ number_format($implantBuyer->totalPaid(), 2) }}</h3>
                    <span class="small text-muted">{{ $implantBuyer->payments->count() }} payments settled</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 border-start border-danger border-4 h-100">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold">Current Outstanding Balance</span>
                    <h3 class="fw-bold mb-0 {{ $due > 0 ? 'text-danger' : 'text-success' }}">
                        PKR {{ number_format($due, 2) }}
                    </h3>
                    <span class="small text-muted">
                        {{ $due > 0 ? 'Payable to Dr Badshah Afsar' : 'All accounts settled' }}
                    </span>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 border-start border-dark border-4 h-100">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold">Failed Implants</span>
                    <h3 class="fw-bold mb-0 text-dark">{{ $implantBuyer->totalFailedQuantity() }} <span class="fs-6 fw-normal text-muted">unit(s)</span></h3>
                    <span class="small text-danger fw-semibold">PKR {{ number_format($implantBuyer->totalFailedCost(), 2) }} (Separate)</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Sales History & Payments Ledger --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-receipt text-primary me-2"></i>Sales Invoices History</h6>
            <a href="{{ route('implant-sales.create', ['buyer_id' => $implantBuyer->id]) }}" class="btn btn-sm btn-outline-primary">
                + Create Sale
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Sale #</th>
                            <th>Date</th>
                            <th>Implants Delivered</th>
                            <th>Total (PKR)</th>
                            <th>Paid (PKR)</th>
                            <th>Due (PKR)</th>
                            <th>Status</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($implantBuyer->sales as $sale)
                            <tr>
                                <td class="ps-3 fw-bold">
                                    <a href="{{ route('implant-sales.show', $sale) }}" class="text-primary text-decoration-none">
                                        {{ $sale->sale_number }}
                                    </a>
                                </td>
                                <td>{{ $sale->sale_date->format('d M Y') }}</td>
                                <td>
                                    @foreach ($sale->items as $item)
                                        <div class="small">
                                            <span class="fw-semibold">{{ $item->quantity }}x</span> {{ $item->implant?->fullSpecification() }}
                                        </div>
                                    @endforeach
                                </td>
                                <td class="fw-bold">PKR {{ number_format($sale->total_amount, 2) }}</td>
                                <td class="text-success">PKR {{ number_format($sale->paid_amount, 2) }}</td>
                                <td class="fw-bold {{ $sale->due_amount > 0 ? 'text-danger' : 'text-muted' }}">
                                    PKR {{ number_format($sale->due_amount, 2) }}
                                </td>
                                <td>
                                    <span class="badge bg-{{ $sale->statusColor() }}-subtle text-{{ $sale->statusColor() }}">
                                        {{ $sale->statusLabel() }}
                                    </span>
                                </td>
                                <td class="text-end pe-3">
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
                                    <a href="{{ route('implant-sales.print', $sale) }}" target="_blank" class="btn btn-sm btn-outline-dark" title="Print Challan">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">No sales invoices recorded for this doctor.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Failed Implants Recorded for this Doctor (Calculated Separately) --}}
    <div class="card shadow-sm border-0 mb-4 border-start border-danger border-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <div>
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-x-octagon text-danger me-2"></i>Failed Implants Recorded
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle ms-2">Calculated Separately</span>
                </h6>
                <span class="small text-muted">Isolated complication records &amp; warranty claims for this doctor/clinic.</span>
            </div>
            <a href="{{ route('failed-implants.create', ['buyer_id' => $implantBuyer->id]) }}" class="btn btn-sm btn-outline-danger">
                + Report Failure
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Failure #</th>
                            <th>Date</th>
                            <th>Implant Model</th>
                            <th>Patient / Tooth</th>
                            <th>Stage &amp; Reason</th>
                            <th class="text-end">Cost / Loss (PKR)</th>
                            <th>Warranty Resolution</th>
                            <th class="text-end pe-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($implantBuyer->failedImplants as $failed)
                            <tr>
                                <td class="ps-3 fw-bold">
                                    <a href="{{ route('failed-implants.show', $failed) }}" class="text-danger text-decoration-none">
                                        {{ $failed->failure_number }}
                                    </a>
                                </td>
                                <td>{{ $failed->failure_date->format('d M Y') }}</td>
                                <td>{{ $failed->implant?->fullSpecification() }}</td>
                                <td>
                                    @if ($failed->tooth_number)
                                        <span class="badge bg-light text-dark border me-1">#{{ $failed->tooth_number }}</span>
                                    @endif
                                    <span class="small text-muted">{{ $failed->patient_name ?: '—' }}</span>
                                </td>
                                <td>
                                    <div class="small fw-semibold">{{ $failed->reasonLabel() }}</div>
                                    <div class="small text-muted">{{ $failed->stageLabel() }}</div>
                                </td>
                                <td class="text-end fw-bold text-danger">PKR {{ number_format($failed->total_cost, 2) }}</td>
                                <td>
                                    <span class="badge bg-{{ $failed->warrantyColor() }}-subtle text-{{ $failed->warrantyColor() }} border border-{{ $failed->warrantyColor() }}-subtle">
                                        {{ $failed->warrantyLabel() }}
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('failed-implants.show', $failed) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    No implant failures recorded for this doctor.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
