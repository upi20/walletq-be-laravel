<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\Account;
use App\Models\TransactionCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'account_id' => Account::factory(),
            'transaction_category_id' => TransactionCategory::factory(),
            'type' => $this->faker->randomElement(['income', 'expense']),
            'amount' => $this->faker->numberBetween(10000, 1000000),
            'date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'note' => $this->faker->sentence(),
            'flag' => Transaction::FLAG_NORMAL
        ];
    }
}
