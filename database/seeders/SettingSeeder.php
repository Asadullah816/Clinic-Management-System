<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'clinic_name' => 'Dr Badshah Afsar',
            'address' => 'Dental Care & Surgery Clinic, Main Boulevard, Gulberg',
            'phone' => '03489030035',
            'email' => 'info@drbadshahafsar.com',
            'currency' => 'PKR',
        ];

        foreach ($defaults as $key => $value) {
            Setting::set($key, $value);
        }
    }
}
