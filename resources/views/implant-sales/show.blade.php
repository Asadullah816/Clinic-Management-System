@extends('layouts.app')

@section('title', $implantSale->sale_number . ' - Dental Implant Sale Invoice')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h4 class="mb-0 fw-bold text-dark">{{ $implantSale->sale_number }}</h4>
                <span class="badge bg-{{ $implantSale->statusColor() }}-subtle text-{{ $implantSale->statusColor() }} border border-{{ $implantSale->statusColor() }}-subtle px-2 py-1">
                    {{ $implantSale->statusLabel() }}
                </span>
            </div>
            <p class="text-muted small mb-0">
                Issued to <strong class="text-dark">{{ $implantSale->buyer->displayName() }}</strong>
                on {{ $implantSale->sale_date->format('l, d M Y') }}
            </p>
        </div>
        <div class="d-flex gap-2">
            @if ($implantSale->due_amount > 0 && $implantSale->whatsappReminderUrl())
                <a href="{{ $implantSale->whatsappReminderUrl() }}" target="_blank"
                    class="btn btn-success" style="background-color: #25D366; border-color: #25D366;">
                    <i class="bi bi-whatsapp me-1"></i> Send WhatsApp Reminder
                </a>
            @endif

            @if ($implantSale->due_amount > 0)
                <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#receivePayModal">
                    <i class="bi bi-cash-coin me-1"></i> Receive Payment
                </button>
            @endif

            <a href="{{ route('implant-sales.print', $implantSale) }}" target="_blank" class="btn btn-dark">
                <i class="bi bi-printer me-1"></i> Print Challan
            </a>
            <a href="{{ route('implant-sales.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> All Sales
            </a>
        </div>
    </div>

    {{-- Doctor & Order Details Card --}}
    <div class="row g-3 mb-4">
        <div class="col-md-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-person-badge text-primary me-2"></i>Doctor / Buyer Details</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered mb-0">
                        <tbody>
                            <tr>
                                <th class="w-25 bg-light">Doctor Name</th>
                                <td class="fw-bold text-primary">
                                    <a href="{{ route('implant-buyers.show', $implantSale->buyer) }}" class="text-decoration-none">
                                        {{ $implantSale->buyer->doctor_name }}
                                    </a>
                                    @if ($implantSale->buyer->is_internal)
                                        <span class="badge bg-primary ms-1">IN-HOUSE / ADMIN USE</span>
                                    @endif
                                </td>
                                <th class="w-25 bg-light">Clinic / Hospital</th>
                                <td>{{ $implantSale->buyer->clinic_hospital_name ?: '—' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Phone / WhatsApp</th>
                                <td>{{ $implantSale->buyer->phone ?: 'No phone registered' }}</td>
                                <th class="bg-light">Patient Reference</th>
                                <td>{{ $implantSale->patient_reference ?: '—' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Clinic Address</th>
                                <td>{{ $implantSale->buyer->address ?: '—' }}</td>
                                <th class="bg-light">Created By</th>
                                <td>{{ $implantSale->createdBy->name ?? 'System' }} on {{ $implantSale->created_at->format('d M Y, H:i') }}</td>
                            </tr>
                            @if ($implantSale->notes)
                                <tr>
                                    <th class="bg-light">Sale Notes</th>
                                    <td colspan="3">{{ $implantSale->notes }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-wallet2 text-success me-2"></i>Financial Summary</h6>
                </div>
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Subtotal (Gross)</span>
                        <span class="fw-semibold">PKR {{ number_format($implantSale->subtotal, 2) }}</span>
                    </div>
                    @if ($implantSale->discount > 0)
                        <div class="d-flex justify-content-between py-2 border-bottom text-muted">
                            <span>Discount Given</span>
                            <span>- PKR {{ number_format($implantSale->discount, 2) }}</span>
                        </div>
                    @endif
                    <div class="d-flex justify-content-between py-2 border-bottom bg-light px-2 rounded mt-1">
                        <span class="fw-bold text-dark">Grand Total</span>
                        <span class="fw-bold fs-5 text-primary">PKR {{ number_format($implantSale->total_amount, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Amount Settled</span>
                        <span class="fw-bold text-success">PKR {{ number_format($implantSale->paid_amount, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-3">
                        <span class="fw-bold {{ $implantSale->due_amount > 0 ? 'text-danger' : 'text-dark' }}">
                            {{ $implantSale->due_amount > 0 ? 'Outstanding Due' : 'Account Balance' }}
                        </span>
                        <span class="fw-bold fs-5 {{ $implantSale->due_amount > 0 ? 'text-danger' : 'text-success' }}">
                            PKR {{ number_format($implantSale->due_amount, 2) }}
                        </span>
                    </div>

                    @if ($implantSale->due_amount > 0)
                        <button type="button" class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#receivePayModal">
                            <i class="bi bi-cash-coin me-1"></i> Receive Due Payment
                        </button>
                    @else
                        <div class="alert alert-success py-2 px-3 small text-center mb-0">
                            <i class="bi bi-check-circle me-1"></i> Invoice Fully Settled
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Implants Delivered Items Table --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-box-seam text-primary me-2"></i>Delivered Dental Implants</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Code</th>
                            <th>Implant Product Specification</th>
                            <th>Brand</th>
                            <th>Dimensions (Ø × L)</th>
                            <th>Platform</th>
                            <th class="text-center">Quantity</th>
                            <th class="text-end">Unit Price</th>
                            <th class="text-end pe-3">Line Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($implantSale->items as $idx => $item)
                            <tr>
                                <td class="ps-3 text-muted">{{ $idx + 1 }}</td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $item->implant?->item_code ?? 'IMP' }}</span>
                                </td>
                                <td class="fw-bold text-dark">
                                    {{ $item->implant?->name ?? 'Implant Item' }}
                                    @if ($item->implant?->material)
                                        <div class="small text-muted">{{ $item->implant->material }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                        {{ $item->implant?->brand ?: '—' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-semibold">
                                        {{ $item->implant?->diameter ? 'Ø ' . $item->implant->diameter : '' }}
                                        {{ $item->implant?->diameter && $item->implant?->length ? ' × ' : '' }}
                                        {{ $item->implant?->length ? 'L: ' . $item->implant->length : '' }}
                                    </span>
                                </td>
                                <td>{{ $item->implant?->platform ?: ($item->implant?->connection_type ?: '—') }}</td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border px-3 py-1 fs-6">{{ $item->quantity }}</span>
                                </td>
                                <td class="text-end">PKR {{ number_format($item->unit_price, 2) }}</td>
                                <td class="text-end pe-3 fw-bold text-dark">PKR {{ number_format($item->total_price, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="6" class="text-end fw-bold">Totals:</td>
                            <td class="text-center fw-bold">{{ $implantSale->items->sum('quantity') }} units</td>
                            <td class="text-end text-muted small">Subtotal:</td>
                            <td class="text-end pe-3 fw-bold fs-6">PKR {{ number_format($implantSale->subtotal, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- Payment Transactions History --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-clock-history text-success me-2"></i>Payment Receipts History</h6>
            @if ($implantSale->due_amount > 0)
                <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#receivePayModal">
                    + Receive Payment
                </button>
            @endif
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Receipt #</th>
                            <th>Date</th>
                            <th>Method</th>
                            <th>Reference #</th>
                            <th>Amount Paid</th>
                            <th>Received By</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($implantSale->payments as $pay)
                            <tr>
                                <td class="ps-3 fw-bold text-primary">{{ $pay->payment_number }}</td>
                                <td>{{ $pay->payment_date->format('d M Y') }}</td>
                                <td>
                                    <span class="badge bg-light text-dark border text-uppercase">{{ str_replace('_', ' ', $pay->payment_method) }}</span>
                                </td>
                                <td>{{ $pay->reference_number ?: '—' }}</td>
                                <td class="fw-bold text-success">PKR {{ number_format($pay->amount, 2) }}</td>
                                <td>{{ $pay->createdBy->name ?? 'System' }}</td>
                                <td class="small text-muted">{{ $pay->notes ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    No payment has been recorded against this invoice yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Receive Payment Modal --}}
    @if ($implantSale->due_amount > 0)
        <div class="modal fade" id="receivePayModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <form method="POST" action="{{ route('implant-payments.store') }}" class="modal-content">
                    @csrf
                    <input type="hidden" name="implant_sale_id" value="{{ $implantSale->id }}">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Receive Payment - {{ $implantSale->sale_number }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="p-3 rounded bg-light border mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Doctor:</span>
                                <span class="fw-bold">{{ $implantSale->buyer->doctor_name }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Invoice Total:</span>
                                <span class="fw-bold">PKR {{ number_format($implantSale->total_amount, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Paid So Far:</span>
                                <span class="text-success fw-bold">PKR {{ number_format($implantSale->paid_amount, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between border-top pt-2 mt-2">
                                <span class="fw-bold text-danger">Remaining Due:</span>
                                <span class="fw-bold text-danger fs-6">PKR {{ number_format($implantSale->due_amount, 2) }}</span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Payment Amount (PKR) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0.01" max="{{ $implantSale->due_amount }}"
                                name="amount" value="{{ $implantSale->due_amount }}" class="form-control form-control-lg fw-bold text-success" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Payment Method <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select" required>
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="online">Online / EasyPaisa / JazzCash</option>
                                <option value="cheque">Cheque</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Transaction / Slip Reference # (Optional)</label>
                            <input type="text" name="reference_number" class="form-control" placeholder="e.g. Bank slip #, TID">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Notes regarding this payment..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-lg me-1"></i> Confirm &amp; Save Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection
