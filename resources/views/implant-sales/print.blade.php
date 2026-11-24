<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Implant Sale Challan - {{ $implantSale->sale_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; margin: 0; padding: 0; }
        }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: #f8fafc; color: #0f172a; }
        .invoice-box { max-width: 800px; margin: 20px auto; background: #fff; padding: 36px; border: 1px solid #e2e8f0; border-radius: 8px; }
        .divider { border-top: 1px solid #e2e8f0; margin: 16px 0; }
        .table th { background-color: #f1f5f9; font-weight: 600; font-size: 0.85rem; }
    </style>
</head>
<body>
    <div class="text-center py-3 no-print">
        <button onclick="window.print()" class="btn btn-primary btn-sm px-4">
            Print Challan / Invoice
        </button>
        <button onclick="window.close()" class="btn btn-outline-secondary btn-sm ms-2">
            Close
        </button>
    </div>

    <div class="invoice-box shadow-sm">
        {{-- Clinic Brand Header --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 d-flex align-items-center justify-content-center p-2"
                    style="width: 48px; height: 48px; background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);">
                    @include('partials.dental-logo', ['width' => 30, 'height' => 30])
                </div>
                <div>
                    <h4 class="mb-0 fw-bold text-primary">{{ \App\Models\Setting::get('clinic_name', config('app.name', 'Dr Badshah Afsar')) }}</h4>
                    <div class="text-muted small">Dental Surgery, Implantology &amp; Oral Care</div>
                    <div class="text-muted small">Ph: {{ \App\Models\Setting::get('phone', '03489030035') }} &bull; {{ \App\Models\Setting::get('address', 'Clinic Address') }}</div>
                </div>
            </div>
            <div class="text-end">
                <span class="badge bg-primary fs-6 px-3 py-2 text-uppercase">Dental Implants Challan</span>
                <div class="fw-bold fs-5 mt-1">{{ $implantSale->sale_number }}</div>
                <div class="text-muted small">Date: {{ $implantSale->sale_date->format('d M Y') }}</div>
            </div>
        </div>

        <div class="divider"></div>

        {{-- Doctor Billed To --}}
        <div class="row mb-3">
            <div class="col-7">
                <div class="text-muted small fw-semibold text-uppercase">Supplied / Billed To:</div>
                <h5 class="fw-bold mb-1">{{ $implantSale->buyer->doctor_name }}</h5>
                @if ($implantSale->buyer->clinic_hospital_name)
                    <div class="text-dark">{{ $implantSale->buyer->clinic_hospital_name }}</div>
                @endif
                @if ($implantSale->buyer->phone)
                    <div class="small text-muted">Phone: {{ $implantSale->buyer->phone }}</div>
                @endif
                @if ($implantSale->buyer->address)
                    <div class="small text-muted">{{ $implantSale->buyer->address }}</div>
                @endif
            </div>
            <div class="col-5 text-end small">
                @if ($implantSale->patient_reference)
                    <div><strong>Patient Reference:</strong> {{ $implantSale->patient_reference }}</div>
                @endif
                <div><strong>Invoice Date:</strong> {{ $implantSale->sale_date->format('d M Y') }}</div>
                <div><strong>Payment Status:</strong> {{ $implantSale->statusLabel() }}</div>
            </div>
        </div>

        {{-- Items Table --}}
        <table class="table table-bordered align-middle mb-3">
            <thead>
                <tr>
                    <th class="text-center" style="width: 40px;">#</th>
                    <th>Implant Specification (Brand &amp; Model)</th>
                    <th class="text-center">Diameter (Ø)</th>
                    <th class="text-center">Length</th>
                    <th class="text-center">Qty</th>
                    <th class="text-end">Unit Price</th>
                    <th class="text-end">Total Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($implantSale->items as $idx => $item)
                    <tr>
                        <td class="text-center small">{{ $idx + 1 }}</td>
                        <td>
                            <div class="fw-bold text-dark">{{ $item->implant?->name }}</div>
                            <div class="small text-muted">
                                {{ $item->implant?->brand ?: 'Brand' }} &bull; {{ $item->implant?->platform ?: 'Standard Platform' }}
                                @if ($item->implant?->lot_number) (Lot: {{ $item->implant->lot_number }}) @endif
                            </div>
                        </td>
                        <td class="text-center fw-semibold">{{ $item->implant?->diameter ? $item->implant->diameter . ' mm' : '—' }}</td>
                        <td class="text-center fw-semibold">{{ $item->implant?->length ? $item->implant->length . ' mm' : '—' }}</td>
                        <td class="text-center fw-bold">{{ $item->quantity }}</td>
                        <td class="text-end">PKR {{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-end fw-bold">PKR {{ number_format($item->total_price, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Summary Totals --}}
        <div class="row justify-content-end mb-4">
            <div class="col-6">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td class="text-muted">Subtotal:</td>
                        <td class="text-end fw-semibold">PKR {{ number_format($implantSale->subtotal, 2) }}</td>
                    </tr>
                    @if ($implantSale->discount > 0)
                        <tr>
                            <td class="text-muted">Discount:</td>
                            <td class="text-end text-danger">- PKR {{ number_format($implantSale->discount, 2) }}</td>
                        </tr>
                    @endif
                    <tr class="border-top">
                        <td class="fw-bold fs-6">Grand Total:</td>
                        <td class="text-end fw-bold fs-6 text-primary">PKR {{ number_format($implantSale->total_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Amount Paid:</td>
                        <td class="text-end text-success fw-bold">PKR {{ number_format($implantSale->paid_amount, 2) }}</td>
                    </tr>
                    <tr class="border-top">
                        <td class="fw-bold {{ $implantSale->due_amount > 0 ? 'text-danger' : 'text-dark' }}">
                            {{ $implantSale->due_amount > 0 ? 'Balance Due:' : 'Balance Due:' }}
                        </td>
                        <td class="text-end fw-bold {{ $implantSale->due_amount > 0 ? 'text-danger' : 'text-success' }}">
                            PKR {{ number_format($implantSale->due_amount, 2) }}
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="divider"></div>

        {{-- Signatures & Notes --}}
        <div class="row pt-4 text-center">
            <div class="col-6">
                <div style="border-top: 1px dashed #94a3b8; width: 60%; margin: 40px auto 0;"></div>
                <div class="small text-muted mt-1">Doctor / Receiving Signature</div>
            </div>
            <div class="col-6">
                <div style="border-top: 1px dashed #94a3b8; width: 60%; margin: 40px auto 0;"></div>
                <div class="small text-muted mt-1">Authorized Signature / Stamp<br><strong>{{ \App\Models\Setting::get('clinic_name', config('app.name', 'Dr Badshah Afsar')) }}</strong></div>
            </div>
        </div>
    </div>
</body>
</html>
