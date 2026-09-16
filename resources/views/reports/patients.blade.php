@extends('layouts.app')

@section('title', 'Patient Report')

@section('content')

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0">Patient Report</h5>
            <button type="button" class="btn btn-outline-dark btn-sm no-print" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print
            </button>
        </div>

        <div class="card-body">

            <form method="GET" action="{{ route('reports.patients') }}" class="row g-2 mb-3 no-print">
                <div class="col-md-5">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                        placeholder="Search patient name or #...">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-outline-secondary"><i class="bi bi-search me-1"></i>
                        Filter</button>
                    <a href="{{ route('reports.patients') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Patient</th>
                            <th>Visits</th>
                            <th>Treatment Cost</th>
                            <th>Total Paid</th>
                            <th>Total Due</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($patients as $patient)
                            <tr>
                                <td>
                                    <a href="{{ route('patients.show', $patient) }}"
                                        class="text-decoration-none fw-semibold">
                                        {{ $patient->full_name }}
                                    </a>
                                    <div class="text-muted small">{{ $patient->patient_number }}</div>
                                </td>
                                <td><span class="badge text-bg-light text-dark">{{ $patient->completed_visits }}</span></td>
                                <td>{{ number_format($patient->treatment_cost_total ?? 0, 2) }}</td>
                                <td class="text-success">{{ number_format($patient->paid_total ?? 0, 2) }}</td>
                                <td class="{{ ($patient->due_total ?? 0) > 0 ? 'text-danger fw-semibold' : '' }}">
                                    {{ number_format($patient->due_total ?? 0, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No patients found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $patients->links() }}

            <div class="text-muted small">
                Visits = completed appointments. Figures cover the patient's full history.
            </div>
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
