@extends('layouts.app')

@section('title', 'Patient Treatments')

@section('content')

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <div>
                <h5 class="mb-0 fw-bold">{{ auth()->user()->isDoctor() ? 'My Patient Treatments' : 'Patient Treatments' }}</h5>
                @if (auth()->user()->isDoctor())
                    <span class="text-muted small">Showing treatments performed by you</span>
                @endif
            </div>
            <a href="{{ route('patient-treatments.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Record Treatment
            </a>
        </div>

        <div class="card-body">

            <form method="GET" action="{{ route('patient-treatments.index') }}" class="row g-2 mb-3">
                <div class="{{ auth()->user()->isDoctor() ? 'col-md-9' : 'col-md-5' }}">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                        placeholder="Search patient name or #...">
                </div>
                @if (! auth()->user()->isDoctor() && isset($doctors))
                    <div class="col-md-4">
                        <select name="doctor_id" class="form-select">
                            <option value="">All Doctors</option>
                            @foreach ($doctors as $doc)
                                <option value="{{ $doc->id }}" {{ (string) request('doctor_id') === (string) $doc->id ? 'selected' : '' }}>
                                    {{ $doc->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
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
                            <th>Treatment &amp; Prescribed Medicines</th>
                            <th>Procedure Pricing</th>
                            <th>Medicines Pricing</th>
                            <th>Grand Total</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($patientTreatments as $patientTreatment)
                            <tr>
                                <td class="text-nowrap">{{ $patientTreatment->treatment_date->format('d M Y') }}</td>
                                <td>
                                    <a href="{{ route('patients.show', $patientTreatment->patient) }}"
                                        class="fw-semibold text-decoration-none">
                                        {{ $patientTreatment->patient->full_name }}
                                    </a>
                                    <div class="small text-muted">{{ $patientTreatment->patient->patient_number }}</div>
                                    @if ($patientTreatment->doctor)
                                        <div class="mt-1">
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-1 py-0" style="font-size: 0.7rem;">
                                                <i class="bi bi-person-badge"></i> {{ $patientTreatment->doctor->name }}
                                            </span>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $patientTreatment->procedure_title }}</div>
                                    @if ($patientTreatment->treatmentMedicines->isNotEmpty())
                                        <div class="mt-1">
                                            @foreach ($patientTreatment->treatmentMedicines->take(3) as $tm)
                                                <span class="badge bg-light text-dark border me-1 small">
                                                    <i class="bi bi-capsule text-success me-1"></i>{{ $tm->quantity }}x {{ $tm->medicine->name ?? 'Product' }}
                                                </span>
                                            @endforeach
                                            @if ($patientTreatment->treatmentMedicines->count() > 3)
                                                <span class="badge bg-secondary-subtle text-secondary small">
                                                    +{{ $patientTreatment->treatmentMedicines->count() - 3 }} more
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="small text-muted fst-italic">No medicines prescribed</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold text-primary">PKR {{ number_format($patientTreatment->price, 2) }}</div>
                                    @if ((float) $patientTreatment->discount > 0)
                                        <div class="small text-danger">Disc: &minus;PKR {{ number_format($patientTreatment->discount, 2) }}</div>
                                    @endif
                                    <div class="small text-muted">Net: PKR {{ number_format($patientTreatment->treatment_net, 2) }}</div>
                                </td>
                                <td>
                                    @if ((float) $patientTreatment->medicine_price > 0 || $patientTreatment->treatmentMedicines->isNotEmpty())
                                        <div class="fw-semibold text-success">PKR {{ number_format($patientTreatment->medicine_price, 2) }}</div>
                                        @if ((float) $patientTreatment->medicine_discount > 0)
                                            <div class="small text-danger">Disc: &minus;PKR {{ number_format($patientTreatment->medicine_discount, 2) }}</div>
                                        @endif
                                        <div class="small text-muted">Net: PKR {{ number_format($patientTreatment->medicine_net, 2) }}</div>
                                    @else
                                        <span class="text-muted small">&mdash;</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-bold fs-6 text-dark">PKR {{ number_format($patientTreatment->total_amount, 2) }}</div>
                                    @if ((float) $patientTreatment->medicine_total > 0)
                                        <div class="small text-muted text-nowrap">Proc: PKR {{ number_format($patientTreatment->treatment_net, 2) }} + Med: PKR {{ number_format($patientTreatment->medicine_net, 2) }}</div>
                                    @endif
                                </td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('patient-treatments.edit', $patientTreatment) }}"
                                        class="btn btn-sm btn-outline-primary" title="Edit"><i
                                            class="bi bi-pencil"></i></a>
                                    <form method="POST"
                                        action="{{ route('patient-treatments.destroy', $patientTreatment) }}"
                                        class="d-inline" onsubmit="return confirm('Delete this treatment record and restore product stock?');">
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
