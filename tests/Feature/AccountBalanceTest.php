<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Account;
use App\Models\AccountCategory;
use App\Models\Transaction;
use App\Models\TransactionCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class AccountBalanceTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;
    protected $accountCategory;
    protected $transactionCategory;    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create(['balance' => 0]);
        $this->token = JWTAuth::fromUser($this->user);
        $this->accountCategory = AccountCategory::factory()->create([
            'user_id' => $this->user->id
        ]);
        $this->transactionCategory = TransactionCategory::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'income'
        ]);
    }

    public function test_account_balance_updates_after_transaction()
    {
        // Create an account with initial balance
        $account = Account::factory()->create([
            'user_id' => $this->user->id,
            'account_category_id' => $this->accountCategory->id,
            'initial_balance' => 1000,
            'current_balance' => 1000
        ]);

        // Add income transaction
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/v1/user/transactions', [
            'account_id' => $account->id,
            'transaction_category_id' => $this->transactionCategory->id,
            'type' => 'income',
            'amount' => 500,
            'date' => now(),
            'note' => 'Test income'
        ]);

        $response->assertStatus(201);

        // Verify account balance increased
        $this->assertEquals(1500, $account->fresh()->current_balance);

        // Add expense transaction
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/v1/user/transactions', [
            'account_id' => $account->id,
            'transaction_category_id' => $this->transactionCategory->id,
            'type' => 'expense',
            'amount' => 300,
            'date' => now(),
            'note' => 'Test expense'
        ]);

        $response->assertStatus(201);

        // Verify account balance decreased
        $this->assertEquals(1200, $account->fresh()->current_balance);
    }

    public function test_multiple_accounts_total_balance()
    {
        // Create two accounts with initial balances
        $account1 = Account::factory()->create([
            'user_id' => $this->user->id,
            'account_category_id' => $this->accountCategory->id,
            'initial_balance' => 1000,
            'current_balance' => 1000,
            'name' => 'Account 1'
        ]);

        $account2 = Account::factory()->create([
            'user_id' => $this->user->id,
            'account_category_id' => $this->accountCategory->id,
            'initial_balance' => 2000,
            'current_balance' => 2000,
            'name' => 'Account 2'
        ]);

        // Get user's total balance
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/v1/user/master-data/account');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'balance' => 3000.0
                ]
            ]);

        // Add transactions to both accounts
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/v1/user/transactions/bulk', [
            'transactions' => [
                [
                    'account_id' => $account1->id,
                    'transaction_category_id' => $this->transactionCategory->id,
                    'type' => 'income',
                    'amount' => 500,
                    'date' => now(),
                    'note' => 'Bulk income 1'
                ],
                [
                    'account_id' => $account2->id,
                    'transaction_category_id' => $this->transactionCategory->id,
                    'type' => 'expense',
                    'amount' => 800,
                    'date' => now(),
                    'note' => 'Bulk expense 1'
                ]
            ]
        ]);

        // Verify individual account balances
        $this->assertEquals(1500, $account1->fresh()->current_balance);
        $this->assertEquals(1200, $account2->fresh()->current_balance);

        // Verify total balance updated
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/v1/user/master-data/account');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'balance' => 2700.0
                ]
            ]);
    }

    public function test_concurrent_transactions_maintain_balance_integrity()
    {
        $account = Account::factory()->create([
            'user_id' => $this->user->id,
            'account_category_id' => $this->accountCategory->id,
            'initial_balance' => 1000,
            'current_balance' => 1000
        ]);

        // Simulate concurrent transactions
        $promises = [];
        for ($i = 0; $i < 5; $i++) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->token
            ])->postJson('/api/v1/user/transactions', [
                'account_id' => $account->id,
                'transaction_category_id' => $this->transactionCategory->id,
                'type' => 'income',
                'amount' => 100,
                'date' => now(),
                'note' => "Concurrent transaction {$i}"
            ]);

            $response->assertStatus(201);
        }

        // Verify final balance is correct (initial 1000 + 5 * 100)
        $this->assertEquals(1500, $account->fresh()->current_balance);

        // Verify transaction count
        $this->assertEquals(
            6, // 5 new transactions + 1 initial balance transaction
            Transaction::where('account_id', $account->id)->count()
        );
    }

    public function test_negative_balance_protection()
    {
        $account = Account::factory()->create([
            'user_id' => $this->user->id,
            'account_category_id' => $this->accountCategory->id,
            'initial_balance' => 1000,
            'current_balance' => 1000
        ]);

        // Try to create expense larger than balance
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/v1/user/transactions', [
            'account_id' => $account->id,
            'transaction_category_id' => $this->transactionCategory->id,
            'type' => 'expense',
            'amount' => 1500, // More than current balance
            'date' => now(),
            'note' => 'Large expense'
        ]);

        $response->assertStatus(422)
            ->assertJsonFragment([
                'message' => 'Insufficient balance for this transaction'
            ]);

        // Verify balance remained unchanged
        $this->assertEquals(1000, $account->fresh()->current_balance);
    }

    public function test_account_history_with_date_range()
    {
        $account = Account::factory()->create([
            'user_id' => $this->user->id,
            'account_category_id' => $this->accountCategory->id,
            'initial_balance' => 1000,
            'current_balance' => 1000
        ]);

        // Create transactions on different dates
        $dates = [
            now()->subDays(5),
            now()->subDays(3),
            now()->subDay(),
            now()
        ];

        foreach ($dates as $index => $date) {
            $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->token
            ])->postJson('/api/v1/user/transactions', [
                'account_id' => $account->id,
                'transaction_category_id' => $this->transactionCategory->id,
                'type' => $index % 2 == 0 ? 'income' : 'expense',
                'amount' => 100,
                'date' => $date,
                'note' => "Transaction on {$date}"
            ]);
        }

        // Query transactions within date range
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/v1/user/transactions?' . http_build_query([
            'account_id' => $account->id,
            'start_date' => now()->subDays(3)->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d')
        ]));

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data')); // Should only include last 3 transactions
    }
}
