<?php

namespace Database\Seeders;

use App\Models\Medicine;
use App\Models\MedicineCategory;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        // ---- Categories (from the spec) ----
        $categories = ['Cream', 'Serum', 'Injection', 'Medical Supply', 'Skin Care', 'Other'];

        foreach ($categories as $name) {
            MedicineCategory::updateOrCreate(
                ['name' => $name],
                ['status' => 'active']
            );
        }

        // ---- Demo supplier ----
        $supplier = Supplier::updateOrCreate(
            ['email' => 'sales@dermasupply.test'],
            [
                'name'    => 'DermaSupply Co.',
                'company' => 'DermaSupply Co. Ltd.',
                'phone'   => '0300-1234567',
                'address' => '12 Pharma Market, Karachi',
                'status'  => 'active',
            ]
        );

        $categoryId = fn (string $name) => MedicineCategory::where('name', $name)->value('id');

        // ---- Demo products ----
        $medicines = [
            [
                'name' => 'Sunscreen SPF 50', 'generic_name' => 'Broad Spectrum Sunscreen',
                'category' => 'Skin Care', 'unit' => 'tube',
                'purchase_price' => 800, 'selling_price' => 1200,
                'stock_quantity' => 25, 'minimum_stock' => 10,
                'expiry_date' => now()->addYear()->toDateString(),
            ],
            [
                'name' => 'Vitamin C Serum', 'generic_name' => 'Ascorbic Acid 10%',
                'category' => 'Serum', 'unit' => 'bottle',
                'purchase_price' => 1500, 'selling_price' => 2200,
                'stock_quantity' => 15, 'minimum_stock' => 5,
                'expiry_date' => now()->addMonths(8)->toDateString(),
            ],
            [
                'name' => 'Retinol Cream', 'generic_name' => 'Retinol 0.5%',
                'category' => 'Cream', 'unit' => 'tube',
                'purchase_price' => 1000, 'selling_price' => 1600,
                'stock_quantity' => 12, 'minimum_stock' => 5,
                'expiry_date' => now()->addMonths(10)->toDateString(),
            ],
            [
                'name' => 'Acne Cream', 'generic_name' => 'Benzoyl Peroxide 2.5%',
                'category' => 'Cream', 'unit' => 'tube',
                'purchase_price' => 400, 'selling_price' => 650,
                'stock_quantity' => 4, 'minimum_stock' => 10,   // deliberately LOW STOCK
                'expiry_date' => now()->addMonths(6)->toDateString(),
            ],
            [
                'name' => 'Medical Gloves (box)', 'generic_name' => 'Nitrile Examination Gloves',
                'category' => 'Medical Supply', 'unit' => 'box',
                'purchase_price' => 900, 'selling_price' => 0,
                'stock_quantity' => 30, 'minimum_stock' => 10,
                'expiry_date' => now()->addYears(3)->toDateString(),
            ],
            [
                'name' => 'Anesthetic Cream', 'generic_name' => 'Lidocaine 5%',
                'category' => 'Cream', 'unit' => 'tube',
                'purchase_price' => 700, 'selling_price' => 1100,
                'stock_quantity' => 8, 'minimum_stock' => 5,
                'expiry_date' => now()->subMonth()->toDateString(), // deliberately EXPIRED
            ],
        ];

        foreach ($medicines as $medicine) {
            Medicine::updateOrCreate(
                ['name' => $medicine['name']],
                [
                    'generic_name'         => $medicine['generic_name'],
                    'medicine_category_id' => $categoryId($medicine['category']),
                    'supplier_id'          => $supplier->id,
                    'unit'                 => $medicine['unit'],
                    'purchase_price'       => $medicine['purchase_price'],
                    'selling_price'        => $medicine['selling_price'],
                    'stock_quantity'       => $medicine['stock_quantity'],
                    'minimum_stock'        => $medicine['minimum_stock'],
                    'expiry_date'          => $medicine['expiry_date'],
                    'status'               => 'active',
                ]
            );
        }
    }
}
