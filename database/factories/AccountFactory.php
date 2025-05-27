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
            'account_category_id' => AccountCategory::factory(),            'name' => $this->faker->unique()->words(2, true),
            'initial_balance' => function (array $attributes) {
                return $attributes['current_balance'] ?? $this->faker->numberBetween(0, 10000000);
            },
            'current_balance' => function (array $attributes) {
                return $attributes['initial_balance'] ?? $this->faker->numberBetween(0, 10000000);
            }
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Account $account) {
            if ($account->initial_balance != 0) {
                // Get or create initial balance transaction category
                $transactionCategory = \App\Models\TransactionCategory::firstOrCreate(
                    [
                        'name' => \App\Models\Transaction::CATEGORY_INITIAL_BALANCE,
                        'user_id' => $account->user_id
                    ],
                    [
                        'type' => 'income',
                        'is_default' => true,
                        'is_hide' => true
                    ]
                );

                // Create initial balance transaction
                \App\Models\Transaction::create([
                    'user_id' => $account->user_id,
                    'account_id' => $account->id,
                    'transaction_category_id' => $transactionCategory->id,
                    'type' => 'income',
                    'amount' => abs($account->initial_balance),
                    'date' => now(),
                    'flag' => \App\Models\Transaction::FLAG_INITIAL_BALANCE
                ]);
            }
        });
    }
}
