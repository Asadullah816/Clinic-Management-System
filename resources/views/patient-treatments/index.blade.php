@extends('layouts.app')

@section('title', 'Patient Treatments')

@section('content')

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0">Patient Treatments</h5>
            <a href="{{ route('patient-treatments.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Record Treatment
            </a>
        </div>

        <div class="card-body">

            <form method="GET" action="{{ route('patient-treatments.index') }}" class="row g-2 mb-3">
                <div class="col-md-6">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                        placeholder="Search patient name or #...">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-outline-secondary"><i class="bi bi-search me-1"></i>
                        Filter</button>
                    <a href="{{ route('patient-treatments.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Patient</th>
                            <th>Treatment</th>
                            <th>Price</th>
                            <th>Discount</th>
                            <th>Total</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($patientTreatments as $patientTreatment)
                            <tr>
                                <td>{{ $patientTreatment->treatment_date->format('d M Y') }}</td>
                                <td>
                                    <a href="{{ route('patients.show', $patientTreatment->patient) }}"
                                        class="text-decoration-none">
                                        {{ $patientTreatment->patient->full_name }}
                                    </a>
                                </td>
                                <td>{{ $patientTreatment->treatment->name ?? '—' }}</td>
                                <td>{{ number_format($patientTreatment->price, 2) }}</td>
                                <td>{{ number_format($patientTreatment->discount, 2) }}</td>
                                <td class="fw-semibold">{{ number_format($patientTreatment->total_amount, 2) }}</td>
                                <td class="text-end">
                                    <a href="{{ route('patient-treatments.edit', $patientTreatment) }}"
                                        class="btn btn-sm btn-outline-primary" title="Edit"><i
                                            class="bi bi-pencil"></i></a>
                                    <form method="POST"
                                        action="{{ route('patient-treatments.destroy', $patientTreatment) }}"
                                        class="d-inline" onsubmit="return confirm('Delete this treatment record?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i
                                                class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No treatment records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $patientTreatments->links() }}

        </div>
    </div>

@endsection
