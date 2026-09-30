<?php

namespace Database\Seeders;

use App\Models\LaserExpenseCategory;
use App\Models\LaserTreatment;
use Illuminate\Database\Seeder;

class LaserSeeder extends Seeder
{
    public function run(): void
    {
        $treatments = [
            [
                'name' => 'Laser Hair Removal - Full Body',
                'code' => 'LHR-FB',
                'body_area' => 'Full Body',
                'price' => 18000,
                'duration' => 90,
                'description' => 'Comprehensive full-body laser hair reduction using triple-wavelength diode.',
                'status' => 'active',
            ],
            [
                'name' => 'Laser Hair Removal - Full Face',
                'code' => 'LHR-FF',
                'body_area' => 'Full Face',
                'price' => 5000,
                'duration' => 30,
                'description' => 'Cheeks, forehead, upper lip, chin and jawline.',
                'status' => 'active',
            ],
            [
                'name' => 'Laser Hair Removal - Underarms',
                'code' => 'LHR-UA',
                'body_area' => 'Underarms',
                'price' => 3500,
                'duration' => 20,
                'description' => 'Bilateral axillary laser hair reduction.',
                'status' => 'active',
            ],
            [
                'name' => 'Laser Hair Removal - Bikini / Intimate',
                'code' => 'LHR-BIK',
                'body_area' => 'Bikini / Intimate',
                'price' => 6000,
                'duration' => 30,
                'description' => 'Bikini line and intimate area contouring.',
                'status' => 'active',
            ],
            [
                'name' => 'Laser Hair Removal - Beard Shaping (Men)',
                'code' => 'LHR-BS',
                'body_area' => 'Beard Shaping',
                'price' => 4000,
                'duration' => 25,
                'description' => 'Precision neck line and upper cheek beard definition.',
                'status' => 'active',
            ],
            [
                'name' => 'Carbon Laser Peel (Hollywood Glow)',
                'code' => 'LP-CARBON',
                'body_area' => 'Full Face',
                'price' => 8500,
                'duration' => 45,
                'description' => 'Q-Switched Nd:YAG carbon lotion skin rejuvenation and pore reduction.',
                'status' => 'active',
            ],
            [
                'name' => 'Q-Switched Tattoo Removal',
                'code' => 'LP-TATTOO',
                'body_area' => 'General',
                'price' => 7000,
                'duration' => 40,
                'description' => 'Pigment fragmentation and tattoo ink removal.',
                'status' => 'active',
            ],
            [
                'name' => 'CO2 Fractional Laser Resurfacing',
                'code' => 'LP-CO2',
                'body_area' => 'Full Face',
                'price' => 15000,
                'duration' => 60,
                'description' => 'Ablative skin resurfacing for acne scars and skin tightening.',
                'status' => 'active',
            ],
        ];

        foreach ($treatments as $treatment) {
            LaserTreatment::updateOrCreate(
                ['name' => $treatment['name']],
                $treatment
            );
        }

        $categories = [
            ['name' => 'Laser Maintenance & Servicing',      'description' => 'Routine engineering inspection, diode laser calibrations and coolant checks.'],
            ['name' => 'Optics, Handpieces & Cartridges',   'description' => 'Replacement handpieces, spot tips, laser lamps, and optical windows.'],
            ['name' => 'Cooling Gels, Shaving & Consumables', 'description' => 'Ultrasound transmission gel, disposable razors, goggles, and prep supplies.'],
            ['name' => 'Machine Parts & Upgrades',          'description' => 'Component replacements and hardware upgrades.'],
            ['name' => 'Facility & Electricity Share',      'description' => 'Agreed share of high-voltage power and clinic space overheads.'],
            ['name' => 'Marketing & Promotion',             'description' => 'Laser-specific advertising, influencer sessions, and social campaigns.'],
            ['name' => 'Miscellaneous Laser Expenses',      'description' => 'Uncategorized laser operational expenses.'],
        ];

        foreach ($categories as $category) {
            LaserExpenseCategory::updateOrCreate(
                ['name' => $category['name']],
                $category
            );
        }
    }
}
