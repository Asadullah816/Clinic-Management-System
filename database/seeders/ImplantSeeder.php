<?php

namespace Database\Seeders;

use App\Models\Implant;
use App\Models\ImplantBuyer;
use App\Models\ImplantStockTransaction;
use Illuminate\Database\Seeder;

class ImplantSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ensure internal clinic / admin self-use doctor exists
        ImplantBuyer::firstOrCreate(
            ['is_internal' => true],
            [
                'doctor_name' => 'Dr Badshah Afsar',
                'clinic_hospital_name' => 'In-House Clinic / Self-Use',
                'phone' => '03489030035',
                'email' => 'doctor@clinic.test',
                'address' => 'Dr Badshah Afsar Dental Clinic',
                'is_internal' => true,
                'notes' => 'Reserved for in-house clinic surgeries by Dr Badshah Afsar (Admin self-use).',
                'status' => 'active',
            ]
        );

        // 2. Standard Dental Implants Catalog
        $standardImplants = [
            [
                'item_code' => 'IMP-0001',
                'name' => 'BLX Bone Level Tapered',
                'brand' => 'Straumann',
                'diameter' => '3.75',
                'length' => '10.0',
                'platform' => 'Regular CrossFit (RB)',
                'connection_type' => 'TorcFit Connection',
                'material' => 'Roxolid SLActive',
                'lot_number' => 'LOT-STR-9801',
                'purchase_price' => 35000.00,
                'sale_price' => 50000.00,
                'stock_quantity' => 10,
                'min_stock_alert' => 3,
                'notes' => 'Premium Swiss implant for immediate stability in all bone types.',
                'status' => 'active',
            ],
            [
                'item_code' => 'IMP-0002',
                'name' => 'BLX Bone Level Tapered Wide',
                'brand' => 'Straumann',
                'diameter' => '4.5',
                'length' => '10.0',
                'platform' => 'Wide CrossFit (WB)',
                'connection_type' => 'TorcFit Connection',
                'material' => 'Roxolid SLActive',
                'lot_number' => 'LOT-STR-9802',
                'purchase_price' => 35000.00,
                'sale_price' => 50000.00,
                'stock_quantity' => 8,
                'min_stock_alert' => 3,
                'notes' => 'Molar extraction socket immediate placement.',
                'status' => 'active',
            ],
            [
                'item_code' => 'IMP-0003',
                'name' => 'TSIII SA Regular Fixture',
                'brand' => 'Osstem',
                'diameter' => '4.0',
                'length' => '10.0',
                'platform' => 'Regular (Mini/Regular)',
                'connection_type' => 'Internal Hex 11° Morse Taper',
                'material' => 'Grade 4 Titanium SA',
                'lot_number' => 'LOT-OSS-4412',
                'purchase_price' => 18000.00,
                'sale_price' => 28000.00,
                'stock_quantity' => 15,
                'min_stock_alert' => 4,
                'notes' => 'High primary stability with optimal thread pitch.',
                'status' => 'active',
            ],
            [
                'item_code' => 'IMP-0004',
                'name' => 'TSIII SA Regular Fixture Long',
                'brand' => 'Osstem',
                'diameter' => '4.5',
                'length' => '11.5',
                'platform' => 'Regular',
                'connection_type' => 'Internal Hex 11° Morse Taper',
                'material' => 'Grade 4 Titanium SA',
                'lot_number' => 'LOT-OSS-4415',
                'purchase_price' => 18000.00,
                'sale_price' => 28000.00,
                'stock_quantity' => 12,
                'min_stock_alert' => 4,
                'notes' => 'Standard posterior implant fixture.',
                'status' => 'active',
            ],
            [
                'item_code' => 'IMP-0005',
                'name' => 'SuperLine II Fixture',
                'brand' => 'Dentium',
                'diameter' => '4.0',
                'length' => '10.0',
                'platform' => 'Internal Hex',
                'connection_type' => 'Conical Connection',
                'material' => 'SLA Surface Titanium',
                'lot_number' => 'LOT-DNT-3021',
                'purchase_price' => 15000.00,
                'sale_price' => 24000.00,
                'stock_quantity' => 10,
                'min_stock_alert' => 3,
                'notes' => 'Excellent osseointegration and bone response.',
                'status' => 'active',
            ],
            [
                'item_code' => 'IMP-0006',
                'name' => 'AnyRidge Knife Thread',
                'brand' => 'Megagen',
                'diameter' => '4.5',
                'length' => '10.0',
                'platform' => 'Conical 5°',
                'connection_type' => 'Internal Hex',
                'material' => 'Xpeed Surface Titanium',
                'lot_number' => 'LOT-MGN-7733',
                'purchase_price' => 22000.00,
                'sale_price' => 34000.00,
                'stock_quantity' => 6,
                'min_stock_alert' => 2,
                'notes' => 'Superior primary stability in soft bone.',
                'status' => 'active',
            ],
        ];

        foreach ($standardImplants as $item) {
            $implant = Implant::firstOrCreate(
                ['item_code' => $item['item_code']],
                $item
            );

            if ($implant->wasRecentlyCreated && $implant->stock_quantity > 0) {
                ImplantStockTransaction::create([
                    'implant_id' => $implant->id,
                    'type' => 'initial',
                    'quantity' => $implant->stock_quantity,
                    'balance_after' => $implant->stock_quantity,
                    'notes' => 'Initial stock registered via ImplantSeeder',
                ]);
            }
        }
    }
}
