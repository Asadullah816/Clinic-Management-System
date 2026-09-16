<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            // Optional search by name or email
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            // Optional filter by role
            ->when($request->filled('role'), function ($query) use ($request) {
                $query->where('role', $request->role);
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString(); // keeps search/filter values in pagination links

        return view('users.index', [
            'users' => $users,
            'roles' => User::roles(),
        ]);
    }

    public function create()
    {
        return view('users.create', [
            'roles' => User::roles(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role'     => 'required|in:admin,accountant,receptionist,staff',
        ]);

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => $request->password, // hashed automatically by the model cast
            'role'     => $request->role,
        ]);

        return redirect()
            ->route('users.index')
            ->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        return view('users.edit', [
            'user'  => $user,
            'roles' => User::roles(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8', // optional: leave blank to keep current
            'role'     => 'required|in:admin,accountant,receptionist,staff',
        ]);

        $user->name  = $request->name;
        $user->email = $request->email;

        if ($request->filled('password')) {
            $user->password = $request->password; // hashed automatically by the model cast
        }

        // Safety: you cannot change your own role
        if ($user->id !== auth()->id()) {
            $user->role = $request->role;
        }

        $user->save();

        return redirect()
            ->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        // Safety: you cannot delete your own account
        if ($user->id === auth()->id()) {
            return redirect()
                ->route('users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $user->delete(); // soft delete

        return redirect()
            ->route('users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Static reference page showing what each role can access.
     */
    public function roles()
    {
        return view('users.roles');
    }
}
