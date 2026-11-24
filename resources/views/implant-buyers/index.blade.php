@extends('layouts.app')

@section('title', 'Doctors & Clinics Directory - Dental Implants')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h4 class="mb-0 fw-bold text-dark">
                <i class="bi bi-person-badge text-primary me-2"></i>Doctors &amp; Clinics Directory
            </h4>
            <p class="text-muted small mb-0">Manage external doctors purchasing implants, internal clinic use, and their outstandings.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('implant-sales.outstanding') }}" class="btn btn-outline-danger">
                <i class="bi bi-exclamation-circle me-1"></i> Doctor Outstandings
            </a>
            <a href="{{ route('implant-buyers.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Add Doctor / Clinic
            </a>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 border-start border-primary border-4 h-100">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold">External Doctors</span>
                    <h3 class="fw-bold mb-0 text-dark">{{ $stats['total_doctors'] }}</h3>
                    <span class="small text-muted">Active client surgeons</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 border-start border-info border-4 h-100">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold">Total Implants Sold</span>
                    <h3 class="fw-bold mb-0 text-info">PKR {{ number_format($stats['total_sales_value'], 2) }}</h3>
                    <span class="small text-muted">All-time sales</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 border-start border-success border-4 h-100">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold">Payments Received</span>
                    <h3 class="fw-bold mb-0 text-success">PKR {{ number_format($stats['total_paid'], 2) }}</h3>
                    <span class="small text-muted">Collected revenue</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 border-start border-danger border-4 h-100">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold">Total Doctor Dues</span>
                    <h3 class="fw-bold mb-0 {{ $stats['total_due'] > 0 ? 'text-danger' : 'text-muted' }}">
                        PKR {{ number_format($stats['total_due'], 2) }}
                    </h3>
                    <span class="small text-muted">Outstanding receivables</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Search Bar --}}
    <div class="card shadow-sm mb-3">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('implant-buyers.index') }}" class="row g-2">
                <div class="col-md-10">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control"
                            placeholder="Search by doctor name, clinic/hospital, phone, email..."
                            value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-primary flex-grow-1">Search</button>
                    @if(request('search'))
                        <a href="{{ route('implant-buyers.index') }}" class="btn btn-sm btn-outline-secondary" title="Clear">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Buyers Table --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Doctor / Clinic</th>
                            <th>Contact Info</th>
                            <th>Total Purchases</th>
                            <th>Paid Amount</th>
                            <th>Outstanding Due</th>
                            <th>Status</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($buyers as $buyer)
                            @php
                                $due = $buyer->totalDue();
                            @endphp
                            <tr class="{{ $buyer->is_internal ? 'table-primary bg-opacity-10' : '' }}">
                                <td class="ps-3">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle {{ $buyer->is_internal ? 'bg-primary text-white' : 'bg-light text-primary border' }} fw-bold d-flex align-items-center justify-content-center me-2 flex-shrink-0"
                                            style="width: 38px; height: 38px; font-size: 14px;">
                                            {{ strtoupper(substr($buyer->doctor_name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <a href="{{ route('implant-buyers.show', $buyer) }}" class="fw-bold text-dark text-decoration-none">
                                                {{ $buyer->doctor_name }}
                                            </a>
                                            @if ($buyer->is_internal)
                                                <span class="badge bg-primary ms-1" style="font-size: 0.65rem;">IN-HOUSE / ADMIN USE</span>
                                            @endif
                                            @if ($buyer->clinic_hospital_name)
                                                <div class="small text-muted">{{ $buyer->clinic_hospital_name }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if ($buyer->phone)
                                        <div class="small text-dark fw-semibold">
                                            <i class="bi bi-telephone me-1 text-secondary"></i>{{ $buyer->phone }}
                                        </div>
                                    @endif
                                    @if ($buyer->email)
                                        <div class="small text-muted">
                                            <i class="bi bi-envelope me-1 text-secondary"></i>{{ $buyer->email }}
                                        </div>
                                    @endif
                                    @if (!$buyer->phone && !$buyer->email)
                                        <span class="text-muted small">&mdash;</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="fw-bold text-dark">PKR {{ number_format($buyer->totalPurchases(), 2) }}</span>
                                    <div class="small text-muted">{{ $buyer->sales->count() }} sales</div>
                                </td>
                                <td>
                                    <span class="fw-semibold text-success">PKR {{ number_format($buyer->totalPaid(), 2) }}</span>
                                </td>
                                <td>
                                    @if ($due > 0)
                                        <span class="fw-bold text-danger">PKR {{ number_format($due, 2) }}</span>
                                    @else
                                        <span class="badge bg-success-subtle text-success">Fully Cleared</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $buyer->status === 'active' ? 'success' : 'secondary' }}-subtle text-{{ $buyer->status === 'active' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($buyer->status) }}
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    {{-- WhatsApp Reminder Button if due > 0 --}}
                                    @if ($due > 0 && $buyer->whatsappReminderUrl())
                                        <a href="{{ $buyer->whatsappReminderUrl() }}" target="_blank"
                                            class="btn btn-sm btn-success me-1" style="background-color: #25D366; border-color: #25D366;"
                                            title="Send WhatsApp Reminder to {{ $buyer->doctor_name }}">
                                            <i class="bi bi-whatsapp me-1"></i> WhatsApp
                                        </a>
                                    @endif
                                    <a href="{{ route('implant-sales.create', ['buyer_id' => $buyer->id]) }}"
                                        class="btn btn-sm btn-outline-primary me-1" title="New Sale to this Doctor">
                                        <i class="bi bi-cart-plus"></i>
                                    </a>
                                    <a href="{{ route('implant-buyers.show', $buyer) }}"
                                        class="btn btn-sm btn-outline-secondary me-1" title="View Statement & History">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('implant-buyers.edit', $buyer) }}"
                                        class="btn btn-sm btn-outline-secondary me-1" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @if (!$buyer->is_internal)
                                        <form method="POST" action="{{ route('implant-buyers.destroy', $buyer) }}" class="d-inline"
                                            onsubmit="return confirm('Delete doctor {{ $buyer->doctor_name }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-person-badge fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                    No doctors or clinics registered yet.
                                    <div class="mt-2">
                                        <a href="{{ route('implant-buyers.create') }}" class="btn btn-sm btn-primary">
                                            + Register First Doctor
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($buyers->hasPages())
                <div class="p-3 border-top">
                    {{ $buyers->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
