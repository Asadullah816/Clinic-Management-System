<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Administrator', 'email' => 'admin@clinic.test',       'role' => 'admin'],
            ['name' => 'Accountant',    'email' => 'accountant@clinic.test',  'role' => 'accountant'],
            ['name' => 'Receptionist',  'email' => 'receptionist@clinic.test','role' => 'receptionist'],
            ['name' => 'Staff Member',  'email' => 'staff@clinic.test',       'role' => 'staff'],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],          // match on email
                [
                    'name'     => $userData['name'],
                    'role'     => $userData['role'],
                    'password' => 'password',             // hashed automatically by the model cast
                ]
            );
        }
    }
}
