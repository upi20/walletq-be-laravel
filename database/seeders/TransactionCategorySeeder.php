<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TransactionCategory;

class TransactionCategorySeeder extends Seeder
{
    public function run(): void
    {
        $defaultCategories = [
            ['name' => 'Gaji', 'type' => 'income'],
            ['name' => 'Bonus', 'type' => 'income'],
            ['name' => 'Penjualan', 'type' => 'income'],

            ['name' => 'Makan & Minum', 'type' => 'expense'],
            ['name' => 'Transportasi', 'type' => 'expense'],
            ['name' => 'Belanja', 'type' => 'expense'],
            ['name' => 'Tagihan', 'type' => 'expense'],
        ];

        foreach ($defaultCategories as $cat) {
            TransactionCategory::updateOrCreate(
                ['user_id' => null, 'name' => $cat['name'], 'type' => $cat['type']],
                ['is_default' => true]
            );
        }
    }
}
