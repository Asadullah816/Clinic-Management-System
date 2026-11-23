<?php

namespace Database\Seeders;

use App\Models\MedicineCategory;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        // ---- Dental Categories ----
        $categories = [
            'Dental Anesthetics',
            'Restorative Materials',
            'Antibiotics & Analgesics',
            'Surgical & Disposable',
            'Oral Hygiene & Preventive',
            'Other',
        ];

        foreach ($categories as $name) {
            MedicineCategory::updateOrCreate(
                ['name' => $name],
                ['status' => 'active']
            );
        }
    }
}
