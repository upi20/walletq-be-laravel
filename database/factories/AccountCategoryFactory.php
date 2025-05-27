<?php

namespace Database\Factories;

use App\Models\AccountCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountCategoryFactory extends Factory
{
    protected $model = AccountCategory::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->unique()->word(),
            'type' => $this->faker->randomElement(['cash', 'bank', 'e-wallet']),
            'is_default' => false
        ];
    }
}
