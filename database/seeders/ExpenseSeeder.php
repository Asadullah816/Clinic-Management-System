<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Rent', 'Electricity', 'Water', 'Salary',
            'Medicine Purchase', 'Maintenance', 'Other',
        ];

        foreach ($categories as $name) {
            ExpenseCategory::updateOrCreate(
                ['name' => $name],
                ['status' => 'active']
            );
        }
    }
}
