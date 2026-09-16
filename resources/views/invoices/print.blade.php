<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $invoice->invoice_number }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f4f6f9;
        }

        .invoice-sheet {
            max-width: 800px;
            background: #fff;
        }

        @media print {
            body {
                background: #fff;
            }

            .no-print {
                display: none !important;
            }

            .invoice-sheet {
                border: none !important;
                box-shadow: none !important;
                max-width: 100%;
            }
        }
    </style>
</head>

<body class="py-4">

    <div class="invoice-sheet mx-auto p-5 border rounded shadow-sm">

        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h4 class="mb-0">{{ config('app.name', 'Skin Clinic') }}</h4>
                <div class="text-muted small">Aesthetic &amp; Care Clinic</div>
            </div>
            <div class="text-end">
                <h2 class="text-uppercase mb-0">Invoice</h2>
                <div class="fw-semibold">{{ $invoice->invoice_number }}</div>
            </div>
        </div>

        <hr>

        {{-- Patient + meta --}}
        <div class="row mb-4">
            <div class="col-6">
                <div class="text-muted small text-uppercase fw-semibold mb-1">Billed To</div>
                <div class="fw-semibold">{{ $invoice->patient->full_name }}</div>
                <div class="small text-muted">
                    {{ $invoice->patient->patient_number }}<br>
                    {{ $invoice->patient->phone }}<br>
                    {{ $invoice->patient->address ?? '' }}
                </div>
            </div>
            <div class="col-6 text-end small">
                <div class="mb-1">Invoice Date: <strong>{{ $invoice->invoice_date->format('d M Y') }}</strong></div>
                <div>Status:
                    <span class="badge text-bg-{{ $invoice->statusColor() }}">{{ $invoice->statusLabel() }}</span>
                </div>
            </div>
        </div>

        {{-- Amounts --}}
        <table class="table table-bordered">
            <tr>
                <th>Subtotal</th>
                <td class="text-end">{{ number_format($invoice->subtotal, 2) }}</td>
            </tr>
            <tr>
                <th>Discount</th>
                <td class="text-end">&minus; {{ number_format($invoice->discount, 2) }}</td>
            </tr>
            <tr class="table-light">
                <th>Total Amount</th>
                <td class="text-end fw-bold">{{ number_format($invoice->total_amount, 2) }}</td>
            </tr>
            <tr>
                <th>Paid</th>
                <td class="text-end">{{ number_format($invoice->paid_amount, 2) }}</td>
            </tr>
            <tr>
                <th>Amount Due</th>
                <td class="text-end fw-bold">{{ number_format($invoice->due_amount, 2) }}</td>
            </tr>
        </table>

        @if ($invoice->notes)
            <div class="small mb-4">
                <span class="text-muted text-uppercase fw-semibold">Notes:</span>
                {{ $invoice->notes }}
            </div>
        @endif

        {{-- Signature --}}
        <div class="row mt-5">
            <div class="col-6">
                <div class="border-top pt-1 small text-muted" style="width: 220px;">Patient Signature</div>
            </div>
            <div class="col-6 text-end">
                <div class="border-top pt-1 small text-muted d-inline-block" style="width: 220px;">Authorized Signature
                </div>
            </div>
        </div>

        <div class="text-center text-muted small mt-4">
            Printed on {{ now()->format('d M Y, H:i') }}
        </div>
    </div>

    <div class="text-center mt-3 no-print">
        <button type="button" class="btn btn-primary" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Print Invoice
        </button>
        <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-outline-secondary">Back</a>
    </div>

</body>

</html>
