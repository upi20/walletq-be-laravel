<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AccountCategory;

class AccountCategorySeeder extends Seeder
{
    public function run(): void
    {
        $defaultCategories = [
            'Cash',
            'Bank',
            'E-Wallet',
            'Virtual Account',
        ];

        foreach ($defaultCategories as $name) {
            AccountCategory::updateOrCreate(
                ['user_id' => null, 'name' => $name],
                ['is_default' => true]
            );
        }
    }
}
