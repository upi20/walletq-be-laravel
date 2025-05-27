<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\AccountCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountFactory extends Factory
{
    protected $model = Account::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'account_category_id' => AccountCategory::factory(),
            'name' => $this->faker->unique()->words(2, true),
            'initial_balance' => $this->faker->numberBetween(0, 10000000),
            'current_balance' => $this->faker->numberBetween(0, 10000000)
        ];
    }
}
