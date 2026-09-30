<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Receipt {{ $payment->receiptNumber() }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    @php
        $format = request()->query('format', 'pos'); // default to 80mm POS receipt
    @endphp

    <style>
        body {
            background-color: #f1f5f9;
            color: #0f172a;
        }

        /* ---------------- Common Print Rules ---------------- */
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: #fff !important;
                margin: 0 !important;
                padding: 0 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }

        @if ($format === 'pos')
            /* ---------------- 80mm POS Thermal Receipt Styling ---------------- */
            @page {
                size: 80mm auto;
                margin: 0mm;
            }

            .pos-receipt {
                width: 80mm;
                max-width: 100%;
                margin: 15px auto;
                background: #fff;
                padding: 4mm 5mm;
                font-family: 'Courier New', Courier, monospace, 'Segoe UI', system-ui;
                font-size: 13px;
                line-height: 1.35;
                color: #000;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            }

            .receipt-divider {
                border-top: 1px dashed #000;
                margin: 6px 0;
            }

            .receipt-double-divider {
                border-top: 2px dashed #000;
                margin: 6px 0;
            }

            .receipt-row {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                margin-bottom: 2px;
            }

            @media print {
                .pos-receipt {
                    width: 78mm !important;
                    max-width: 78mm !important;
                    box-shadow: none !important;
                    border: none !important;
                    padding: 2mm 3mm !important;
                    margin: 0 auto !important;
                }
            }
        @else
            /* ---------------- Standard A4 Format Styling ---------------- */
            @page {
                size: A4;
                margin: 0mm;
            }

            .receipt-sheet {
                max-width: 720px;
                background: #fff;
                font-family: system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
            }

            @media print {
                .receipt-sheet {
                    border: none !important;
                    box-shadow: none !important;
                    border-radius: 0 !important;
                    max-width: 100% !important;
                    width: 100% !important;
                    margin: 0 !important;
                    padding: 15mm 20mm !important;
                }
            }
        @endif
    </style>
</head>

<body class="py-3">

    {{-- Top Action Toolbar (Never Printed) --}}
    <div class="container text-center mb-3 no-print">
        <div class="d-inline-flex flex-wrap align-items-center gap-2 bg-white p-2 rounded-3 shadow-sm border">
            <button type="button" class="btn btn-primary fw-semibold px-3" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print Receipt
            </button>

            <div class="btn-group" role="group">
                <a href="{{ request()->fullUrlWithQuery(['format' => 'pos']) }}"
                   class="btn btn-sm {{ $format === 'pos' ? 'btn-dark' : 'btn-outline-secondary' }}">
                    <i class="bi bi-receipt me-1"></i> 80mm Receipt (POS)
                </a>
                <a href="{{ request()->fullUrlWithQuery(['format' => 'a4']) }}"
                   class="btn btn-sm {{ $format === 'a4' ? 'btn-dark' : 'btn-outline-secondary' }}">
                    <i class="bi bi-file-earmark-text me-1"></i> A4 Full Page
                </a>
            </div>

            <a href="{{ route('invoices.show', $payment->invoice) }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    @if ($format === 'pos')
        {{-- ==================== 80mm POS Thermal Receipt ==================== --}}
        <div class="pos-receipt border">
            {{-- Header --}}
            <div class="text-center mb-2">
                <div class="fw-bold fs-6 text-uppercase" style="letter-spacing: 0.5px;">
                    {{ \App\Models\Setting::get('clinic_name', config('app.name', 'Mayar Skin Care & Aesthetic Clinic')) }}
                </div>
                @if ($address = \App\Models\Setting::get('address'))
                    <div style="font-size: 11px;">{{ $address }}</div>
                @endif
                <div style="font-size: 11px;">Ph: {{ \App\Models\Setting::get('phone', '03489030035') }}</div>
            </div>

            <div class="receipt-divider"></div>

            <div class="text-center fw-bold text-uppercase" style="letter-spacing: 1px; font-size: 14px;">
                PAYMENT RECEIPT
            </div>

            <div class="receipt-divider"></div>

            {{-- Metadata --}}
            <div class="receipt-row">
                <span>Receipt #:</span>
                <span class="fw-bold">{{ $payment->receiptNumber() }}</span>
            </div>
            <div class="receipt-row">
                <span>Invoice #:</span>
                <span class="fw-bold">{{ $payment->invoice->invoice_number }}</span>
            </div>
            <div class="receipt-row">
                <span>Date:</span>
                <span>{{ $payment->payment_date->format('d/m/Y') }}</span>
            </div>
            <div class="receipt-row">
                <span>Method:</span>
                <span class="fw-bold">{{ $payment->methodLabel() }}</span>
            </div>
            @if ($payment->reference)
                <div class="receipt-row">
                    <span>Reference:</span>
                    <span>{{ $payment->reference }}</span>
                </div>
            @endif
            @if ($payment->receivedBy)
                <div class="receipt-row">
                    <span>Received By:</span>
                    <span>{{ $payment->receivedBy->name }}</span>
                </div>
            @endif

            <div class="receipt-divider"></div>

            {{-- Patient Info --}}
            <div class="receipt-row">
                <span>Patient:</span>
                <span class="fw-bold">{{ $payment->patient->full_name }}</span>
            </div>
            <div class="receipt-row">
                <span>MRN / ID:</span>
                <span>{{ $payment->patient->patient_number }}</span>
            </div>

            <div class="receipt-double-divider"></div>

            {{-- Amount Highlight --}}
            <div class="text-center py-2 my-1" style="border: 1px dashed #000;">
                <div style="font-size: 11px; text-transform: uppercase;">AMOUNT RECEIVED</div>
                <div class="fw-bold" style="font-size: 18px;">PKR {{ number_format($payment->amount, 2) }}</div>
            </div>

            <div class="receipt-divider"></div>

            {{-- Balance Status --}}
            <div class="receipt-row">
                <span>Invoice Total:</span>
                <span>PKR {{ number_format($payment->invoice->total_amount, 2) }}</span>
            </div>
            <div class="receipt-row">
                <span>Total Paid:</span>
                <span>PKR {{ number_format($payment->invoice->paid_amount, 2) }}</span>
            </div>
            <div class="receipt-row fw-bold">
                <span>Remaining Due:</span>
                <span>PKR {{ number_format($payment->invoice->due_amount, 2) }}</span>
            </div>

            <div class="receipt-double-divider"></div>

            {{-- Status Banner --}}
            <div class="text-center my-2 fw-bold" style="font-size: 13px;">
                @if ($payment->invoice->due_amount <= 0)
                    *** INVOICE FULLY CLEARED ***
                @else
                    *** REMAINING BALANCE: PKR {{ number_format($payment->invoice->due_amount, 2) }} ***
                @endif
            </div>

            <div class="receipt-divider"></div>

            {{-- Footer --}}
            <div class="text-center mt-2" style="font-size: 11px;">
                <div>Thank you for your payment!</div>
                <div>Please keep this receipt for verification.</div>
                <div class="text-muted mt-1" style="font-size: 10px;">Printed: {{ now()->format('d M Y, h:i A') }}</div>
            </div>
        </div>
    @else
        {{-- ==================== Standard A4 Receipt ==================== --}}
        <div class="receipt-sheet mx-auto p-5 border rounded shadow-sm">
            <div class="text-center mb-4">
                <h4 class="mb-0 fw-bold text-primary">{{ \App\Models\Setting::get('clinic_name', config('app.name', 'Mayar Skin Care & Aesthetic Clinic')) }}</h4>
                @if ($address = \App\Models\Setting::get('address'))
                    <div class="text-muted small">{{ $address }}</div>
                @endif
                <div class="text-muted small">Ph: {{ \App\Models\Setting::get('phone', '03489030035') }}</div>
                <h5 class="text-uppercase mt-3 mb-0 fw-bold">Payment Receipt</h5>
                <div class="fw-semibold text-secondary">{{ $payment->receiptNumber() }}</div>
            </div>

            <hr>

            <table class="table table-sm align-middle mb-4">
                <tr>
                    <th class="w-25 bg-light">Received From</th>
                    <td class="fw-semibold">{{ $payment->patient->full_name }} ({{ $payment->patient->patient_number }})</td>
                </tr>
                <tr>
                    <th class="bg-light">Invoice</th>
                    <td>
                        <span class="fw-semibold">{{ $payment->invoice->invoice_number }}</span>
                    </td>
                </tr>
                <tr>
                    <th class="bg-light">Payment Date</th>
                    <td>{{ $payment->payment_date->format('d M Y') }}</td>
                </tr>
                <tr>
                    <th class="bg-light">Payment Method</th>
                    <td>{{ $payment->methodLabel() }}</td>
                </tr>
                <tr>
                    <th class="bg-light">Reference</th>
                    <td>{{ $payment->reference ?? '—' }}</td>
                </tr>
            </table>

            <div class="alert alert-success d-flex justify-content-between align-items-center fs-5 shadow-sm">
                <span class="fw-semibold">Amount Received:</span>
                <span class="fw-bold">PKR {{ number_format($payment->amount, 2) }}</span>
            </div>

            <table class="table table-sm table-bordered mb-4">
                <tr>
                    <th class="w-50 bg-light">Invoice Total</th>
                    <td class="text-end">PKR {{ number_format($payment->invoice->total_amount, 2) }}</td>
                </tr>
                <tr>
                    <th class="bg-light">Total Paid (including this payment)</th>
                    <td class="text-end text-success fw-semibold">PKR {{ number_format($payment->invoice->paid_amount, 2) }}</td>
                </tr>
                <tr>
                    <th class="bg-light">Remaining Balance</th>
                    <td class="text-end fw-bold {{ $payment->invoice->due_amount > 0 ? 'text-danger' : '' }}">
                        PKR {{ number_format($payment->invoice->due_amount, 2) }}
                    </td>
                </tr>
            </table>

            <div class="row mt-5 pt-4">
                <div class="col-6">
                    <div class="border-top pt-1 small text-muted" style="width: 200px;">Received By:
                        {{ $payment->receivedBy->name ?? '—' }}</div>
                </div>
                <div class="col-6 text-end">
                    <div class="border-top pt-1 small text-muted d-inline-block" style="width: 200px;">Patient Signature</div>
                </div>
            </div>

            <div class="text-center text-muted small mt-4">
                Printed on {{ now()->format('d M Y, H:i') }}
            </div>
        </div>
    @endif

</body>

</html>
