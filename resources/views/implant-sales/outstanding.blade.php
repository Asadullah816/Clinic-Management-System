@extends('layouts.app')

@section('title', 'Doctor Outstandings - Dental Implants')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h4 class="mb-0 fw-bold text-dark">
                <i class="bi bi-cash-stack text-danger me-2"></i>Doctor Outstandings &amp; WhatsApp Reminders
            </h4>
            <p class="text-muted small mb-0">Review pending implant receivables from doctors and send 1-click WhatsApp payment reminders.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('implant-sales.create') }}" class="btn btn-primary">
                <i class="bi bi-cart-plus me-1"></i> New Implant Sale
            </a>
            <a href="{{ route('implant-sales.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> All Sales
            </a>
        </div>
    </div>

    {{-- Outstanding Total Banner --}}
    <div class="card shadow-sm mb-3 border-0 border-start border-danger border-4 bg-white">
        <div class="card-body p-4 d-flex justify-content-between align-items-center">
            <div>
                <span class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.05em;">Total Pending Receivables from Doctors</span>
                <div class="fs-2 fw-bold text-danger">PKR {{ number_format($totalOutstanding, 2) }}</div>
                <div class="small text-muted">Across all outstanding implant delivery invoices</div>
            </div>
            <div class="text-end">
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 fs-6">
                    <i class="bi bi-hourglass-split me-1"></i> {{ $sales->total() }} Invoices Due
                </span>
            </div>
        </div>
    </div>

    {{-- Filter by Doctor --}}
    <div class="card shadow-sm mb-3">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('implant-sales.outstanding') }}" class="row g-2">
                <div class="col-md-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control"
                            placeholder="Search by sale #, doctor name, clinic, phone..."
                            value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="buyer_id" class="form-select form-select-sm">
                        <option value="">-- All Doctors with Dues --</option>
                        @foreach ($buyers as $b)
                            <option value="{{ $b->id }}" {{ request('buyer_id') == $b->id ? 'selected' : '' }}>
                                {{ $b->displayName() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-primary flex-grow-1">Filter</button>
                    @if(request('search') || request('buyer_id'))
                        <a href="{{ route('implant-sales.outstanding') }}" class="btn btn-sm btn-outline-secondary" title="Clear">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Outstandings Table --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Sale #</th>
                            <th>Date</th>
                            <th>Doctor / Clinic</th>
                            <th>Implants Delivered</th>
                            <th>Total (PKR)</th>
                            <th>Paid (PKR)</th>
                            <th>Due Balance</th>
                            <th class="text-end pe-3">Actions &amp; WhatsApp Reminder</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sales as $sale)
                            <tr>
                                <td class="ps-3 fw-bold">
                                    <a href="{{ route('implant-sales.show', $sale) }}" class="text-primary text-decoration-none">
                                        {{ $sale->sale_number }}
                                    </a>
                                </td>
                                <td>{{ $sale->sale_date->format('d M Y') }}</td>
                                <td>
                                    <a href="{{ route('implant-buyers.show', $sale->buyer) }}" class="fw-bold text-dark text-decoration-none">
                                        {{ $sale->buyer->displayName() }}
                                    </a>
                                    @if ($sale->buyer->phone)
                                        <div class="small text-muted"><i class="bi bi-telephone me-1"></i>{{ $sale->buyer->phone }}</div>
                                    @else
                                        <div class="small text-warning"><i class="bi bi-exclamation-triangle me-1"></i>No phone registered</div>
                                    @endif
                                </td>
                                <td>
                                    @foreach ($sale->items as $item)
                                        <div class="small">
                                            <span class="fw-bold">{{ $item->quantity }}x</span> {{ $item->implant?->fullSpecification() }}
                                        </div>
                                    @endforeach
                                </td>
                                <td>PKR {{ number_format($sale->total_amount, 2) }}</td>
                                <td class="text-success">PKR {{ number_format($sale->paid_amount, 2) }}</td>
                                <td>
                                    <span class="fw-bold text-danger fs-6">
                                        PKR {{ number_format($sale->due_amount, 2) }}
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    {{-- 1-Click WhatsApp Reminder Button --}}
                                    @if ($sale->whatsappReminderUrl())
                                        <a href="{{ $sale->whatsappReminderUrl() }}" target="_blank"
                                            class="btn btn-sm btn-success me-1" style="background-color: #25D366; border-color: #25D366;"
                                            title="Send WhatsApp Reminder to {{ $sale->buyer->doctor_name }}">
                                            <i class="bi bi-whatsapp me-1"></i> Send Reminder
                                        </a>
                                    @else
                                        <button type="button" class="btn btn-sm btn-secondary me-1" disabled title="Doctor phone not registered">
                                            <i class="bi bi-whatsapp me-1"></i> No Phone
                                        </button>
                                    @endif

                                    {{-- Receive Payment Modal trigger --}}
                                    <button type="button" class="btn btn-sm btn-outline-success me-1"
                                        data-bs-toggle="modal" data-bs-target="#payModal{{ $sale->id }}"
                                        title="Receive Payment">
                                        <i class="bi bi-cash-coin me-1"></i> Pay
                                    </button>

                                    <a href="{{ route('implant-sales.show', $sale) }}" class="btn btn-sm btn-outline-secondary" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    {{-- Payment Modal --}}
                                    <div class="modal fade text-start" id="payModal{{ $sale->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <form method="POST" action="{{ route('implant-payments.store') }}" class="modal-content">
                                                @csrf
                                                <input type="hidden" name="implant_sale_id" value="{{ $sale->id }}">
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold">Receive Payment - {{ $sale->sale_number }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="p-3 rounded bg-light border mb-3">
                                                        <div class="d-flex justify-content-between mb-1">
                                                            <span class="text-muted">Doctor / Buyer:</span>
                                                            <span class="fw-bold">{{ $sale->buyer->doctor_name }}</span>
                                                        </div>
                                                        <div class="d-flex justify-content-between mb-1">
                                                            <span class="text-muted">Invoice Total:</span>
                                                            <span class="fw-bold">PKR {{ number_format($sale->total_amount, 2) }}</span>
                                                        </div>
                                                        <div class="d-flex justify-content-between mb-1">
                                                            <span class="text-muted">Already Paid:</span>
                                                            <span class="text-success fw-bold">PKR {{ number_format($sale->paid_amount, 2) }}</span>
                                                        </div>
                                                        <div class="d-flex justify-content-between border-top pt-2 mt-2">
                                                            <span class="fw-bold text-danger">Outstanding Due:</span>
                                                            <span class="fw-bold text-danger fs-6">PKR {{ number_format($sale->due_amount, 2) }}</span>
                                                        </div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Payment Amount (PKR) <span class="text-danger">*</span></label>
                                                        <input type="number" step="0.01" min="0.01" max="{{ $sale->due_amount }}"
                                                            name="amount" value="{{ $sale->due_amount }}" class="form-control" required>
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
                                                        <label class="form-label fw-semibold">Transaction / Reference # (Optional)</label>
                                                        <input type="text" name="reference_number" class="form-control" placeholder="e.g. Bank slip #, TID">
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Notes</label>
                                                        <textarea name="notes" class="form-control" rows="2" placeholder="Payment notes..."></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-success">
                                                        <i class="bi bi-check-lg me-1"></i> Confirm Payment
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-success">
                                    <i class="bi bi-check-circle fs-1 d-block mb-2 text-success opacity-75"></i>
                                    All clear! There are no outstanding payments due from any doctor.
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
