<?php

namespace Database\Factories;

use App\Models\TransactionCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionCategoryFactory extends Factory
{
    protected $model = TransactionCategory::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->unique()->words(2, true),
            'type' => $this->faker->randomElement(['income', 'expense']),
            'is_default' => false,
            'is_hide' => false
        ];
    }
}
