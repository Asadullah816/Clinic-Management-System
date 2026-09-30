<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laser Invoice {{ $session->invoice_number }}</title>
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

            .invoice-sheet {
                max-width: 800px;
                background: #fff;
                font-family: system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
            }

            @media print {
                .invoice-sheet {
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

            <a href="{{ route('laser.sessions.show', $session) }}" class="btn btn-sm btn-outline-secondary">
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
                <div class="fw-bold text-uppercase" style="font-size: 11px;">LASER &amp; AESTHETIC CARE UNIT</div>
                @if ($address = \App\Models\Setting::get('address'))
                    <div style="font-size: 11px;">{{ $address }}</div>
                @endif
                <div style="font-size: 11px;">Ph: {{ \App\Models\Setting::get('phone', '03489030035') }}</div>
            </div>

            <div class="receipt-divider"></div>

            <div class="text-center fw-bold text-uppercase" style="letter-spacing: 1px; font-size: 14px;">
                LASER TREATMENT RECEIPT
            </div>

            <div class="receipt-divider"></div>

            {{-- Metadata --}}
            <div class="receipt-row">
                <span>Invoice #:</span>
                <span class="fw-bold">{{ $session->invoice_number }}</span>
            </div>
            <div class="receipt-row">
                <span>Date:</span>
                <span>{{ $session->session_date->format('d/m/Y') }} {{ now()->format('h:i A') }}</span>
            </div>
            <div class="receipt-row">
                <span>Operator:</span>
                <span>{{ $session->performedBy->name ?? 'Specialist' }}</span>
            </div>

            <div class="receipt-divider"></div>

            {{-- Patient Info --}}
            <div class="receipt-row">
                <span>Patient:</span>
                <span class="fw-bold">{{ $session->patient->full_name }}</span>
            </div>
            <div class="receipt-row">
                <span>MRN / ID:</span>
                <span>{{ $session->patient->patient_number }}</span>
            </div>
            @if ($session->patient->phone)
                <div class="receipt-row">
                    <span>Contact:</span>
                    <span>{{ $session->patient->phone }}</span>
                </div>
            @endif
            @if ($session->patient->skin_type)
                <div class="receipt-row">
                    <span>Skin Type:</span>
                    <span>{{ $session->patient->skin_type }}</span>
                </div>
            @endif

            <div class="receipt-double-divider"></div>

            {{-- Procedure Details --}}
            <div class="mb-2">
                <div class="receipt-row fw-bold" style="font-size: 13px;">
                    <span>{{ $session->treatment->name ?? 'Laser Procedure' }}</span>
                    <span>PKR {{ number_format($session->price, 2) }}</span>
                </div>
                <div class="small text-muted" style="font-size: 11px;">
                    Session #{{ $session->session_number }}{{ $session->total_sessions ? ' of ' . $session->total_sessions : '' }}
                    @if ($session->fluence) &bull; Fluence: {{ $session->fluence }} @endif
                    @if ($session->pulses_count) &bull; Shots: {{ $session->pulses_count }} @endif
                </div>
            </div>

            <div class="receipt-divider"></div>

            {{-- Amounts Summary --}}
            <div class="receipt-row">
                <span>Subtotal:</span>
                <span>PKR {{ number_format($session->price, 2) }}</span>
            </div>

            @if ($session->discount > 0)
                <div class="receipt-row">
                    <span>Laser Discount:</span>
                    <span>- PKR {{ number_format($session->discount, 2) }}</span>
                </div>
            @endif

            <div class="receipt-divider"></div>

            <div class="receipt-row fw-bold" style="font-size: 15px;">
                <span>NET TOTAL:</span>
                <span>PKR {{ number_format($session->total_amount, 2) }}</span>
            </div>

            <div class="receipt-row">
                <span>Amount Paid:</span>
                <span class="fw-bold">PKR {{ number_format($session->paid_amount, 2) }}</span>
            </div>

            <div class="receipt-row fw-bold" style="font-size: 14px;">
                <span>Balance Due:</span>
                <span>PKR {{ number_format($session->due_amount, 2) }}</span>
            </div>

            <div class="receipt-row">
                <span>Method:</span>
                <span class="text-uppercase">{{ $session->methodLabel() }}</span>
            </div>

            <div class="receipt-double-divider"></div>

            {{-- Status Banner --}}
            <div class="text-center my-2 fw-bold" style="font-size: 13px;">
                @if ($session->status === 'paid')
                    *** FULLY CLEARED ***
                @elseif($session->status === 'partial')
                    *** PARTIAL - DUE: PKR {{ number_format($session->due_amount, 2) }} ***
                @else
                    *** DUE: PKR {{ number_format($session->due_amount, 2) }} ***
                @endif
            </div>

            <div class="receipt-divider"></div>

            {{-- Aftercare Advice & Footer --}}
            <div class="text-center mt-2" style="font-size: 11px;">
                <div class="fw-bold text-uppercase mb-1">Laser Post-Care Reminder</div>
                <div>&bull; Avoid direct sun exposure for 48 hrs</div>
                <div>&bull; Apply soothing gel &amp; broad-spectrum sunblock</div>
                <div>&bull; Avoid hot baths, sauna &amp; waxing</div>
                <div class="text-muted mt-2" style="font-size: 10px;">Printed: {{ now()->format('d M Y, h:i A') }}</div>
            </div>
        </div>
    @else
        {{-- ==================== Standard A4 Invoice ==================== --}}
        <div class="invoice-sheet mx-auto p-5 border rounded shadow-sm">
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h4 class="mb-0 fw-bold text-primary">{{ \App\Models\Setting::get('clinic_name', config('app.name', 'Mayar Skin Care & Aesthetic Clinic')) }}</h4>
                    <div class="text-uppercase fw-semibold small text-secondary">Laser &amp; Aesthetic Treatment Unit</div>
                    @if ($address = \App\Models\Setting::get('address'))
                        <div class="text-muted small">{{ $address }}</div>
                    @endif
                    <div class="text-muted small">Ph: {{ \App\Models\Setting::get('phone', '03489030035') }}</div>
                </div>
                <div class="text-end">
                    <h2 class="text-uppercase mb-0 fw-bold">Laser Invoice</h2>
                    <div class="fw-semibold text-secondary">{{ $session->invoice_number }}</div>
                </div>
            </div>

            <hr>

            <div class="row mb-4">
                <div class="col-6">
                    <div class="text-muted small text-uppercase fw-semibold mb-1">Client Information</div>
                    <div class="fw-bold fs-6">{{ $session->patient->full_name }}</div>
                    <div class="small text-muted">
                        Patient ID: {{ $session->patient->patient_number }}<br>
                        Phone: {{ $session->patient->phone }}<br>
                        @if ($session->patient->skin_type)
                            Skin Phototype: <strong>{{ $session->patient->skin_type }}</strong><br>
                        @endif
                        {{ $session->patient->address ?? '' }}
                    </div>
                </div>
                <div class="col-6 text-end small">
                    <div class="mb-1">Session Date: <strong>{{ $session->session_date->format('d M Y') }}</strong></div>
                    <div>Status:
                        <span class="badge text-bg-{{ $session->statusColor() }}">{{ $session->statusLabel() }}</span>
                    </div>
                    <div class="text-muted mt-1">Specialist: {{ $session->performedBy->name ?? 'Laser Tech' }}</div>
                </div>
            </div>

            {{-- Procedure Details Table --}}
            <table class="table table-bordered mb-4">
                <thead class="table-light">
                    <tr>
                        <th>Procedure / Description</th>
                        <th>Session #</th>
                        <th>Parameters</th>
                        <th class="text-end">Amount (PKR)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="fw-bold">{{ $session->treatment->name ?? 'Laser Procedure' }}</div>
                            <div class="small text-muted">{{ $session->laser_machine ?? 'Diode Laser' }}</div>
                        </td>
                        <td>Session {{ $session->session_number }}{{ $session->total_sessions ? ' of ' . $session->total_sessions : '' }}</td>
                        <td class="small">
                            @if ($session->fluence) Fluence: {{ $session->fluence }}<br> @endif
                            @if ($session->pulses_count) Shots: {{ number_format($session->pulses_count) }} @endif
                        </td>
                        <td class="text-end fw-semibold">PKR {{ number_format($session->price, 2) }}</td>
                    </tr>
                </tbody>
            </table>

            {{-- Financial Summary --}}
            <div class="row justify-content-end mb-4">
                <div class="col-md-6">
                    <table class="table table-sm table-bordered">
                        <tr>
                            <th class="bg-light">Subtotal</th>
                            <td class="text-end">PKR {{ number_format($session->price, 2) }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">Laser Discount</th>
                            <td class="text-end text-danger">&minus; PKR {{ number_format($session->discount, 2) }}</td>
                        </tr>
                        <tr class="table-light">
                            <th class="fs-6">Net Total</th>
                            <td class="text-end fw-bold fs-6">PKR {{ number_format($session->total_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">Amount Paid</th>
                            <td class="text-end text-success fw-bold">PKR {{ number_format($session->paid_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">Balance Due</th>
                            <td class="text-end fw-bold {{ $session->due_amount > 0 ? 'text-danger' : 'text-dark' }}">
                                PKR {{ number_format($session->due_amount, 2) }}
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            @if ($session->notes)
                <div class="p-3 bg-light rounded border mb-4">
                    <span class="text-muted text-uppercase fw-semibold small d-block mb-1">Session Notes:</span>
                    <div class="small">{{ $session->notes }}</div>
                </div>
            @endif

            {{-- Signatures --}}
            <div class="row mt-5 pt-4">
                <div class="col-6">
                    <div class="border-top pt-1 small text-muted" style="width: 220px;">Client Signature</div>
                </div>
                <div class="col-6 text-end">
                    <div class="border-top pt-1 small text-muted d-inline-block" style="width: 220px;">Laser Specialist Signature</div>
                </div>
            </div>

            <div class="text-center text-muted small mt-4">
                Printed on {{ now()->format('d M Y, H:i') }}
            </div>
        </div>
    @endif

</body>

</html>
