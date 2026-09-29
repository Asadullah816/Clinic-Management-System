<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can update another user', function () {
    $admin = User::create([
        'name' => 'Admin User',
        'email' => 'admin@test.com',
        'password' => 'password123',
        'role' => 'admin',
    ]);

    $staff = User::create([
        'name' => 'Staff Old',
        'email' => 'staff@test.com',
        'password' => 'password123',
        'role' => 'staff',
    ]);

    $response = $this->actingAs($admin)->put(route('users.update', $staff), [
        'name' => 'Staff Updated',
        'email' => 'staff@test.com',
        'role' => 'accountant',
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('users.index'));

    $staff->refresh();
    expect($staff->name)->toBe('Staff Updated');
    expect($staff->role)->toBe('accountant');
});

test('admin updating own user without role input fails if role is disabled in view', function () {
    $admin = User::create([
        'name' => 'Admin User',
        'email' => 'admin@test.com',
        'password' => 'password123',
        'role' => 'admin',
    ]);

    // Simulating submitting the form when role select is disabled (browser does not send role)
    $response = $this->actingAs($admin)->put(route('users.update', $admin), [
        'name' => 'Admin Updated',
        'email' => 'admin@test.com',
        // role is omitted because select was disabled!
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('users.index'));

    $admin->refresh();
    expect($admin->name)->toBe('Admin Updated');
    expect($admin->role)->toBe('admin');
});
