<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            TreatmentSeeder::class,
            InventorySeeder::class,
            ExpenseSeeder::class,
             SettingSeeder::class,
        ]);
    }
}
