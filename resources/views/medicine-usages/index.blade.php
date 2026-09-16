@extends('layouts.app')

@section('title', 'Medicine Usage')

@section('content')

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0">Medicine / Product Usage</h5>
            <a href="{{ route('medicine-usages.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Record Usage
            </a>
        </div>

        <div class="card-body">

            <form method="GET" action="{{ route('medicine-usages.index') }}" class="row g-2 mb-3">
                <div class="col-md-4">
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
                <div class="col-md-4">
                    <select name="medicine_id" class="form-select">
                        <option value="">All products</option>
                        @foreach ($medicines as $medicine)
                            <option value="{{ $medicine->id }}"
                                {{ request('medicine_id') == $medicine->id ? 'selected' : '' }}>
                                {{ $medicine->name }}{{ $medicine->trashed() ? ' (deleted)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-outline-secondary"><i class="bi bi-search me-1"></i>
                        Filter</button>
                    <a href="{{ route('medicine-usages.index') }}" class="btn btn-outline-secondary">Reset</a>
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
                            <th>Qty</th>
                            <th>Used By</th>
                            <th>Notes</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($usages as $usage)
                            <tr>
                                <td>{{ $usage->usage_date->format('d M Y') }}</td>
                                <td>
                                    <a href="{{ route('patients.show', $usage->patient) }}" class="text-decoration-none">
                                        {{ $usage->patient->full_name }}
                                    </a>
                                </td>
                                <td>{{ $usage->treatment->name ?? '—' }}</td>
                                <td>{{ $usage->medicine->name }}</td>
                                <td class="fw-semibold">{{ $usage->quantity }}</td>
                                <td>{{ $usage->usedBy->name ?? '—' }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($usage->notes ?? '—', 25) }}</td>
                                <td class="text-end">
                                    <form method="POST" action="{{ route('medicine-usages.destroy', $usage) }}"
                                        class="d-inline"
                                        onsubmit="return confirm('Delete this usage record? The product stock will be restored.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                            title="Delete (restores stock)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">No usage records yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $usages->links() }}

        </div>
    </div>

@endsection
