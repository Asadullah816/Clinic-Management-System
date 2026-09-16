<?php

namespace Database\Seeders;

use App\Models\Treatment;
use Illuminate\Database\Seeder;

class TreatmentSeeder extends Seeder
{
    public function run(): void
    {
        $treatments = [
            ['name' => 'Facial',          'price' => 3000,  'duration' => 60, 'description' => 'Deep cleansing facial'],
            ['name' => 'Laser Treatment', 'price' => 8000,  'duration' => 45, 'description' => 'Laser hair / skin treatment'],
            ['name' => 'PRP',             'price' => 12000, 'duration' => 90, 'description' => 'Platelet-rich plasma therapy'],
            ['name' => 'Hydrafacial',     'price' => 6500,  'duration' => 60, 'description' => 'Hydrating multi-step facial'],
            ['name' => 'Chemical Peel',   'price' => 5000,  'duration' => 40, 'description' => 'Chemical exfoliation peel'],
        ];

        foreach ($treatments as $treatment) {
            Treatment::updateOrCreate(
                ['name' => $treatment['name']],
                $treatment + ['status' => 'active']
            );
        }
    }
}
