<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Receipt {{ $payment->receiptNumber() }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f4f6f9;
        }

        .receipt-sheet {
            max-width: 700px;
            background: #fff;
        }

        @media print {
            body {
                background: #fff;
            }

            .no-print {
                display: none !important;
            }

            .receipt-sheet {
                border: none !important;
                box-shadow: none !important;
                max-width: 100%;
            }
        }
    </style>
</head>

<body class="py-4">

    <div class="receipt-sheet mx-auto p-5 border rounded shadow-sm">

        <div class="text-center mb-4">
            <h4 class="mb-0">{{ config('app.name', 'Skin Clinic') }}</h4>
            <div class="text-muted small">Aesthetic &amp; Care Clinic</div>
            <h5 class="text-uppercase mt-3 mb-0">Payment Receipt</h5>
            <div class="fw-semibold">{{ $payment->receiptNumber() }}</div>
        </div>

        <hr>

        <table class="table table-sm align-middle">
            <tr>
                <th class="w-25">Received From</th>
                <td>{{ $payment->patient->full_name }} ({{ $payment->patient->patient_number }})</td>
            </tr>
            <tr>
                <th>Invoice</th>
                <td>
                    <a
                        href="{{ route('invoices.show', $payment->invoice) }}">{{ $payment->invoice->invoice_number }}</a>
                </td>
            </tr>
            <tr>
                <th>Payment Date</th>
                <td>{{ $payment->payment_date->format('d M Y') }}</td>
            </tr>
            <tr>
                <th>Payment Method</th>
                <td>{{ $payment->methodLabel() }}</td>
            </tr>
            <tr>
                <th>Reference</th>
                <td>{{ $payment->reference ?? '—' }}</td>
            </tr>
        </table>

        <div class="alert alert-success d-flex justify-content-between align-items-center fs-5">
            <span class="fw-semibold">Amount Received:</span>
            <span class="fw-bold">{{ number_format($payment->amount, 2) }}</span>
        </div>

        <table class="table table-sm table-bordered">
            <tr>
                <th>Invoice Total</th>
                <td class="text-end">{{ number_format($payment->invoice->total_amount, 2) }}</td>
            </tr>
            <tr>
                <th>Total Paid (including this payment)</th>
                <td class="text-end">{{ number_format($payment->invoice->paid_amount, 2) }}</td>
            </tr>
            <tr>
                <th>Remaining Balance</th>
                <td class="text-end fw-bold">{{ number_format($payment->invoice->due_amount, 2) }}</td>
            </tr>
        </table>

        <div class="row mt-5">
            <div class="col-6">
                <div class="border-top pt-1 small text-muted" style="width: 200px;">Received By:
                    {{ $payment->receivedBy->name ?? '—' }}</div>
            </div>
            <div class="col-6 text-end">
                <div class="border-top pt-1 small text-muted d-inline-block" style="width: 200px;">Patient Signature
                </div>
            </div>
        </div>

        <div class="text-center text-muted small mt-4">
            Printed on {{ now()->format('d M Y, H:i') }}
        </div>
    </div>

    <div class="text-center mt-3 no-print">
        <button type="button" class="btn btn-primary" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Print Receipt
        </button>
        <a href="{{ route('invoices.show', $payment->invoice) }}" class="btn btn-outline-secondary">Back</a>
    </div>

</body>

</html>
