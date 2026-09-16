@extends('layouts.app')

@section('title', 'Medicine Usage Report')

@section('content')

    <div class="card shadow-sm mb-3 border-start border-primary border-4">
        <div class="card-body d-flex justify-content-between align-items-center py-3">
            <div>
                <div class="text-muted small">Total Quantity Used @if (request()->filled('from') ||
                        request()->filled('to') ||
                        request()->filled('patient_id') ||
                        request()->filled('medicine_id'))
                        (for current filter)
                    @endif
                </div>
                <div class="fs-3 fw-bold text-primary">{{ number_format($filteredQuantity) }}</div>
            </div>
            <i class="bi bi-capsule fs-1 text-primary opacity-25"></i>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0">Medicine / Product Usage Report</h5>
            <button type="button" class="btn btn-outline-dark btn-sm no-print" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print
            </button>
        </div>

        <div class="card-body">

            <form method="GET" action="{{ route('reports.usage') }}" class="row g-2 mb-3 no-print">
                <div class="col-md-2">
                    <input type="date" name="from" value="{{ request('from') }}" class="form-control"
                        title="From date">
                </div>
                <div class="col-md-2">
                    <input type="date" name="to" value="{{ request('to') }}" class="form-control" title="To date">
                </div>
                <div class="col-md-3">
                    <select name="patient_id" class="form-select">
                        <option value="">All patients</option>
                        @foreach ($patients as $patient)
                            <option value="{{ $patient->id }}"
                                {{ request('patient_id') == $patient->id ? 'selected' : '' }}>
                                {{ $patient->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="medicine_id" class="form-select">
                        <option value="">All products</option>
                        @foreach ($medicines as $medicine)
                            <option value="{{ $medicine->id }}"
                                {{ request('medicine_id') == $medicine->id ? 'selected' : '' }}>
                                {{ $medicine->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-secondary w-100"><i class="bi bi-search me-1"></i>
                        Filter</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Patient</th>
                            <th>Treatment</th>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Used By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($usages as $usage)
                            <tr>
                                <td>{{ $usage->usage_date->format('d M Y') }}</td>
                                <td>{{ $usage->patient->full_name }}</td>
                                <td>{{ $usage->treatment->name ?? '—' }}</td>
                                <td>{{ $usage->medicine->name }}</td>
                                <td class="fw-semibold">{{ $usage->quantity }}</td>
                                <td>{{ $usage->usedBy->name ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No usage records found for this
                                    filter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $usages->links() }}

        </div>
    </div>

    <style>
        @media print {

            .sidebar,
            .navbar,
            footer,
            .no-print {
                display: none !important;
            }

            .main-wrapper {
                margin-left: 0 !important;
            }

            body {
                background: #fff !important;
            }

            .card {
                border: none !important;
                box-shadow: none !important;
            }
        }
    </style>

@endsection
