@extends('layouts.app')

@section('title', 'Users')

@section('content')

    @php
        $roleColors = [
            \App\Models\User::ROLE_ADMIN => 'danger',
            \App\Models\User::ROLE_ACCOUNTANT => 'warning',
            \App\Models\User::ROLE_RECEPTIONIST => 'info',
            \App\Models\User::ROLE_STAFF => 'secondary',
        ];
    @endphp

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0">Users</h5>
            <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Add User
            </a>
        </div>

        <div class="card-body">

            {{-- Search & role filter --}}
            <form method="GET" action="{{ route('users.index') }}" class="row g-2 mb-3">
                <div class="col-md-5">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                        placeholder="Search by name or email...">
                </div>
                <div class="col-md-3">
                    <select name="role" class="form-select">
                        <option value="">All roles</option>
                        @foreach ($roles as $roleValue => $roleLabel)
                            <option value="{{ $roleValue }}" {{ request('role') === $roleValue ? 'selected' : '' }}>
                                {{ $roleLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-outline-secondary">
                        <i class="bi bi-search me-1"></i> Filter
                    </button>
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Created</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td class="fw-semibold">
                                    {{ $user->name }}
                                    @if ($user->id === auth()->id())
                                        <span class="badge text-bg-primary ms-1">You</span>
                                    @endif
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    <span class="badge text-bg-{{ $roleColors[$user->role] ?? 'secondary' }}">
                                        {{ \App\Models\User::roles()[$user->role] ?? $user->role }}
                                    </span>
                                </td>
                                <td>{{ $user->created_at->format('d M Y') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-primary"
                                        title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    @if ($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('users.destroy', $user) }}" class="d-inline"
                                            onsubmit="return confirm('Delete user &quot;{{ $user->name }}&quot;?');">
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
                                <td colspan="5" class="text-center text-muted py-4">No users found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $users->links() }}

        </div>
    </div>

@endsection
