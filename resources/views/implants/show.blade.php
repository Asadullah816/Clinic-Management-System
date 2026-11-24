@extends('layouts.app')

@section('title', $implant->name . ' - Dental Implant Details')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h4 class="mb-0 fw-bold text-dark">{{ $implant->name }}</h4>
                <span class="badge bg-light text-dark border">{{ $implant->item_code }}</span>
                <span class="badge bg-{{ $implant->stockStatusColor() }}">{{ $implant->stockStatusLabel() }}</span>
            </div>
            <p class="text-muted small mb-0">{{ $implant->fullSpecification() }}</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#restockModal">
                <i class="bi bi-box-arrow-in-down me-1"></i> Replenish Stock
            </button>
            <a href="{{ route('implant-sales.create') }}" class="btn btn-outline-primary">
                <i class="bi bi-cart-plus me-1"></i> Record Sale
            </a>
            <a href="{{ route('implants.edit', $implant) }}" class="btn btn-outline-secondary">
                <i class="bi bi-pencil me-1"></i> Edit
            </a>
            <a href="{{ route('implants.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Inventory
            </a>
        </div>
    </div>

    {{-- Implant Overview & Specs --}}
    <div class="row g-3 mb-4">
        <div class="col-md-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-info-circle text-primary me-2"></i>Technical Specifications</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered mb-0">
                        <tbody>
                            <tr>
                                <th class="w-25 bg-light">Brand / Maker</th>
                                <td class="fw-bold text-primary">{{ $implant->brand ?: 'Not specified' }}</td>
                                <th class="w-25 bg-light">Model Name</th>
                                <td>{{ $implant->name }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Diameter (Ø)</th>
                                <td class="fw-bold">{{ $implant->diameter ? $implant->diameter . ' mm' : '—' }}</td>
                                <th class="bg-light">Length</th>
                                <td class="fw-bold">{{ $implant->length ? $implant->length . ' mm' : '—' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Platform</th>
                                <td>{{ $implant->platform ?: '—' }}</td>
                                <th class="bg-light">Connection</th>
                                <td>{{ $implant->connection_type ?: '—' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Material / Surface</th>
                                <td>{{ $implant->material ?: '—' }}</td>
                                <th class="bg-light">Lot / Batch #</th>
                                <td>{{ $implant->lot_number ?: '—' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Clinical Notes</th>
                                <td colspan="3">{{ $implant->notes ?: 'No description provided.' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-cash-stack text-success me-2"></i>Stock &amp; Pricing</h6>
                </div>
                <div class="card-body p-3">
                    <div class="p-3 rounded bg-light border mb-3">
                        <div class="text-muted small">Current Stock on Hand</div>
                        <div class="fs-2 fw-bold text-{{ $implant->stockStatusColor() }}">
                            {{ $implant->stock_quantity }} <span class="fs-6 text-muted">units</span>
                        </div>
                        <div class="small text-muted mt-1">Alert threshold: {{ $implant->min_stock_alert }} units</div>
                    </div>

                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Sale Price (Doctor)</span>
                        <span class="fw-bold text-dark">PKR {{ number_format($implant->sale_price, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Purchase / Cost Price</span>
                        <span class="fw-semibold text-secondary">PKR {{ number_format($implant->purchase_price, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Profit Margin / Unit</span>
                        <span class="fw-bold text-success">
                            PKR {{ number_format(max(0, $implant->sale_price - $implant->purchase_price), 2) }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between py-2">
                        <span class="text-muted">Total Stock Value</span>
                        <span class="fw-bold text-primary">
                            PKR {{ number_format($implant->stock_quantity * $implant->purchase_price, 2) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Stock Movement Ledger & Recent Sales --}}
    <div class="row g-3">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-clock-history text-secondary me-2"></i>Stock Transaction Ledger</h6>
                    <span class="badge bg-light text-secondary border">{{ $implant->stockTransactions->count() }} records</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 380px; overflow-y: auto;">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Qty</th>
                                    <th>Balance</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($implant->stockTransactions as $tx)
                                    <tr>
                                        <td>{{ $tx->created_at->format('d M Y, H:i') }}</td>
                                        <td>
                                            @if ($tx->type === 'sale')
                                                <span class="badge bg-danger-subtle text-danger">Sale / Dispatch</span>
                                            @elseif ($tx->type === 'purchase_restock')
                                                <span class="badge bg-success-subtle text-success">Restock</span>
                                            @elseif ($tx->type === 'initial')
                                                <span class="badge bg-primary-subtle text-primary">Initial Stock</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary">{{ ucfirst($tx->type) }}</span>
                                            @endif
                                        </td>
                                        <td class="fw-bold {{ $tx->quantity > 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $tx->quantity > 0 ? '+' : '' }}{{ $tx->quantity }}
                                        </td>
                                        <td class="fw-semibold">{{ $tx->balance_after }}</td>
                                        <td class="small text-muted">{{ $tx->notes ?: '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">No stock movements logged.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-bag-check text-primary me-2"></i>Sales History for this Implant</h6>
                    <span class="badge bg-light text-secondary border">{{ $implant->saleItems->count() }} sales</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 380px; overflow-y: auto;">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Sale #</th>
                                    <th>Doctor / Buyer</th>
                                    <th>Qty</th>
                                    <th>Unit Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($implant->saleItems as $sItem)
                                    <tr>
                                        <td>
                                            <a href="{{ route('implant-sales.show', $sItem->sale) }}" class="fw-semibold text-primary text-decoration-none">
                                                {{ $sItem->sale->sale_number }}
                                            </a>
                                            <div class="small text-muted">{{ $sItem->sale->sale_date->format('d M Y') }}</div>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $sItem->sale->buyer->displayName() }}</div>
                                        </td>
                                        <td class="fw-bold">{{ $sItem->quantity }}</td>
                                        <td>PKR {{ number_format($sItem->unit_price, 2) }}</td>
                                        <td class="fw-bold text-dark">PKR {{ number_format($sItem->total_price, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">No sales recorded for this implant yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Restock Modal --}}
    <div class="modal fade" id="restockModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('implants.restock', $implant) }}" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Restock {{ $implant->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-3">
                        Current stock on hand: <strong class="text-dark">{{ $implant->stock_quantity }} units</strong>.
                    </p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Quantity to Add <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" min="1" class="form-control" required placeholder="e.g. 5, 10, 20">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">New Purchase / Cost Price (PKR)</label>
                        <input type="number" step="0.01" name="purchase_price" value="{{ $implant->purchase_price }}" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Lot / Batch Number</label>
                        <input type="text" name="lot_number" value="{{ $implant->lot_number }}" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Supplier delivery or shipment ref..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-lg me-1"></i> Add Stock
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
