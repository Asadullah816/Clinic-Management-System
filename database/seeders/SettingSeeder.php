<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'clinic_name' => 'Mayar skin care & Aesthethic clinic',
            'address' => 'House 12, Main Boulevard, Gulberg',
            'phone' => '03489030035',
            'email' => 'info@skinclinic.test',
            'currency' => 'PKR',
        ];

        foreach ($defaults as $key => $value) {
            Setting::set($key, $value);
        }
    }
}
