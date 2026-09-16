<?php

namespace Database\Seeders;

use App\Models\Expense;
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

        $categoryId = fn (string $name) => ExpenseCategory::where('name', $name)->value('id');

        Expense::updateOrCreate(
            ['title' => 'Monthly Rent', 'expense_date' => now()->startOfMonth()->toDateString()],
            [
                'expense_category_id' => $categoryId('Rent'),
                'amount'              => 80000,
                'expense_date'        => now()->startOfMonth()->toDateString(),
                'payment_method'      => 'bank',
                'description'         => 'Clinic premises monthly rent (demo data)',
                'created_by'          => 1,
            ]
        );

        Expense::updateOrCreate(
            ['title' => 'Electricity Bill', 'expense_date' => now()->startOfMonth()->addDays(4)->toDateString()],
            [
                'expense_category_id' => $categoryId('Electricity'),
                'amount'              => 12500,
                'expense_date'        => now()->startOfMonth()->addDays(4)->toDateString(),
                'payment_method'      => 'online',
                'description'         => 'Utility bill for current month (demo data)',
                'created_by'          => 1,
            ]
        );
    }
}
