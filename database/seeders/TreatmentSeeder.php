<?php

namespace Database\Seeders;

use App\Models\Treatment;
use Illuminate\Database\Seeder;

class TreatmentSeeder extends Seeder
{
    public function run(): void
    {
        $treatments = [
            ['name' => 'Dental Consultation & Examination', 'price' => 1000, 'duration' => 20, 'description' => 'Comprehensive oral examination, diagnostic review, and treatment planning'],
            ['name' => 'Teeth Scaling & Polishing',        'price' => 3500, 'duration' => 45, 'description' => 'Full mouth ultrasonic scaling, stain removal, and polishing'],
            ['name' => 'Tooth Restoration (Filling)',      'price' => 3000, 'duration' => 40, 'description' => 'Tooth-colored light-cure composite restoration for dental caries'],
            ['name' => 'Root Canal Treatment (RCT)',       'price' => 10000, 'duration' => 60, 'description' => 'Complete endodontic therapy including canal instrumentation and obturation'],
            ['name' => 'Simple Tooth Extraction',          'price' => 2500, 'duration' => 30, 'description' => 'Routine painless tooth extraction with local anesthesia'],
            ['name' => 'Surgical Wisdom Tooth Extraction', 'price' => 8500, 'duration' => 60, 'description' => 'Surgical extraction of impacted third molar / wisdom tooth'],
            ['name' => 'Professional Teeth Whitening',     'price' => 15000, 'duration' => 60, 'description' => 'In-clinic cosmetic bleaching and teeth whitening treatment'],
            ['name' => 'Dental Crown (Cap) - Porcelain',   'price' => 9000, 'duration' => 45, 'description' => 'Custom porcelain-fused-to-metal (PFM) crown preparation and fit'],
            ['name' => 'Dental Crown - Zirconia',          'price' => 16000, 'duration' => 45, 'description' => 'Premium high-strength monolithic zirconia aesthetic crown'],
            ['name' => 'Deep Periodontal Curettage',       'price' => 5000, 'duration' => 45, 'description' => 'Subgingival deep cleaning, root planing, and curettage for gum disease'],
            ['name' => 'Orthodontic Consultation',         'price' => 2000, 'duration' => 30, 'description' => 'Dental alignment, bite evaluation, and braces/aligner consultation'],
            ['name' => 'Pediatric Pulpotomy & Fluoride',   'price' => 3500, 'duration' => 35, 'description' => 'Children dental pulpotomy, cavity restoration, and topical fluoride varnish'],
        ];

        foreach ($treatments as $treatment) {
            Treatment::updateOrCreate(
                ['name' => $treatment['name']],
                $treatment + ['status' => 'active']
            );
        }
    }
}
