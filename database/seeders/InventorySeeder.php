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

        // ---- Dental Supplier ----
        $supplier = Supplier::updateOrCreate(
            ['email' => 'sales@dentcare.test'],
            [
                'name' => 'DentCare Medical & Dental Supplies',
                'company' => 'DentCare Supplies Ltd.',
                'phone' => '0300-9876543',
                'address' => '45 Medical & Dental Equipment Market, Lahore',
                'status' => 'active',
            ]
        );

        $categoryId = fn (string $name) => MedicineCategory::where('name', $name)->value('id');

        // ---- Dental Demo Medicines & Materials ----
        $medicines = [
            [
                'name' => 'Lignocaine 2% with Adrenaline',
                'generic_name' => 'Lidocaine HCl 2% + Epinephrine 1:100,000',
                'category' => 'Dental Anesthetics',
                'unit' => 'box',
                'purchase_price' => 2200,
                'selling_price' => 3000,
                'stock_quantity' => 20,
                'minimum_stock' => 5,
                'expiry_date' => now()->addYear()->toDateString(),
            ],
            [
                'name' => 'Articaine 4% Cartridges',
                'generic_name' => 'Articaine HCl 4% with Epinephrine 1:100,000',
                'category' => 'Dental Anesthetics',
                'unit' => 'box',
                'purchase_price' => 3800,
                'selling_price' => 4800,
                'stock_quantity' => 15,
                'minimum_stock' => 5,
                'expiry_date' => now()->addMonths(18)->toDateString(),
            ],
            [
                'name' => 'Composite Resin Syringe (Shade A2)',
                'generic_name' => 'Universal Nano-Hybrid Dental Composite',
                'category' => 'Restorative Materials',
                'unit' => 'syringe',
                'purchase_price' => 1800,
                'selling_price' => 2800,
                'stock_quantity' => 14,
                'minimum_stock' => 4,
                'expiry_date' => now()->addMonths(12)->toDateString(),
            ],
            [
                'name' => 'Glass Ionomer Cement (GIC Type IX)',
                'generic_name' => 'High Viscosity Posterior Glass Ionomer',
                'category' => 'Restorative Materials',
                'unit' => 'pack',
                'purchase_price' => 3200,
                'selling_price' => 4500,
                'stock_quantity' => 8,
                'minimum_stock' => 3,
                'expiry_date' => now()->addMonths(14)->toDateString(),
            ],
            [
                'name' => 'Amoxicillin & Clavulanate (Augmentin 625mg)',
                'generic_name' => 'Amoxicillin 500mg + Clavulanic Acid 125mg',
                'category' => 'Antibiotics & Analgesics',
                'unit' => 'strip',
                'purchase_price' => 380,
                'selling_price' => 520,
                'stock_quantity' => 25,
                'minimum_stock' => 8,
                'expiry_date' => now()->addMonths(10)->toDateString(),
            ],
            [
                'name' => 'Ibuprofen 400mg Tablets',
                'generic_name' => 'Ibuprofen 400mg Analgesic',
                'category' => 'Antibiotics & Analgesics',
                'unit' => 'strip',
                'purchase_price' => 120,
                'selling_price' => 180,
                'stock_quantity' => 40,
                'minimum_stock' => 10,
                'expiry_date' => now()->addMonths(16)->toDateString(),
            ],
            [
                'name' => 'Dental Nitrile Examination Gloves',
                'generic_name' => 'Powder-Free Dental Nitrile Gloves',
                'category' => 'Surgical & Disposable',
                'unit' => 'box',
                'purchase_price' => 950,
                'selling_price' => 1200,
                'stock_quantity' => 30,
                'minimum_stock' => 10,
                'expiry_date' => now()->addYears(3)->toDateString(),
            ],
            [
                'name' => 'Dental Needles 27G Long',
                'generic_name' => 'Sterile Dental Injection Needles 27G',
                'category' => 'Surgical & Disposable',
                'unit' => 'box',
                'purchase_price' => 650,
                'selling_price' => 900,
                'stock_quantity' => 5,
                'minimum_stock' => 10,
                'expiry_date' => now()->addMonths(24)->toDateString(),
            ],
            [
                'name' => 'Chlorhexidine Gluconate 0.2% Mouthwash',
                'generic_name' => 'Chlorhexidine 0.2% Antiseptic Oral Solution',
                'category' => 'Oral Hygiene & Preventive',
                'unit' => 'bottle',
                'purchase_price' => 250,
                'selling_price' => 380,
                'stock_quantity' => 18,
                'minimum_stock' => 5,
                'expiry_date' => now()->addMonths(12)->toDateString(),
            ],
            [
                'name' => 'Dental Etchant Gel 37%',
                'generic_name' => 'Phosphoric Acid 37% Etching Gel',
                'category' => 'Restorative Materials',
                'unit' => 'syringe',
                'purchase_price' => 450,
                'selling_price' => 700,
                'stock_quantity' => 4,
                'minimum_stock' => 5,
                'expiry_date' => now()->subMonth()->toDateString(),
            ],
        ];

        foreach ($medicines as $medicine) {
            Medicine::updateOrCreate(
                ['name' => $medicine['name']],
                [
                    'generic_name' => $medicine['generic_name'],
                    'medicine_category_id' => $categoryId($medicine['category']),
                    'supplier_id' => $supplier->id,
                    'unit' => $medicine['unit'],
                    'purchase_price' => $medicine['purchase_price'],
                    'selling_price' => $medicine['selling_price'],
                    'stock_quantity' => $medicine['stock_quantity'],
                    'minimum_stock' => $medicine['minimum_stock'],
                    'expiry_date' => $medicine['expiry_date'],
                    'status' => 'active',
                ]
            );
        }
    }
}
