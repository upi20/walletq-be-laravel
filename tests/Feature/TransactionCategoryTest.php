<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\TransactionCategory;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class TransactionCategoryTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->token = JWTAuth::fromUser($this->user);
    }

    public function test_can_get_transaction_categories()
    {
        TransactionCategory::factory()->count(3)->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/v1/user/master-data/transaction-category');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'type',
                            'is_default',
                            'is_hide',
                            'created_at',
                            'updated_at'
                        ]
                    ]
                ]);
    }

    public function test_can_filter_transaction_categories_by_type()
    {
        // Create both income and expense categories
        TransactionCategory::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'type' => 'income'
        ]);
        TransactionCategory::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'type' => 'expense'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/v1/user/master-data/transaction-category?type=income');

        $response->assertStatus(200)
                ->assertJsonCount(2, 'data');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/v1/user/master-data/transaction-category?type=expense');

        $response->assertStatus(200)
                ->assertJsonCount(3, 'data');
    }

    public function test_can_create_transaction_category()
    {
        $categoryData = [
            'name' => 'Food & Beverages',
            'type' => 'expense'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/v1/user/master-data/transaction-category', $categoryData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'id',
                        'name',
                        'type',
                        'is_default',
                        'is_hide',
                        'created_at',
                        'updated_at'
                    ]
                ]);

        $this->assertDatabaseHas('transaction_categories', [
            'name' => 'Food & Beverages',
            'type' => 'expense',
            'user_id' => $this->user->id
        ]);
    }

    public function test_cannot_update_default_category()
    {
        $category = TransactionCategory::factory()->create([
            'user_id' => $this->user->id,
            'is_default' => true
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson("/api/v1/user/master-data/transaction-category/{$category->id}", [
            'name' => 'Updated Name',
            'type' => 'expense'
        ]);

        $response->assertStatus(422);
    }

    public function test_cannot_delete_category_with_transactions()
    {
        $category = TransactionCategory::factory()->create([
            'user_id' => $this->user->id
        ]);

        // Create a transaction using this category
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'transaction_category_id' => $category->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->deleteJson("/api/v1/user/master-data/transaction-category/{$category->id}");

        $response->assertStatus(422);
        
        $this->assertDatabaseHas('transaction_categories', [
            'id' => $category->id
        ]);
    }

    public function test_can_delete_unused_category()
    {
        $category = TransactionCategory::factory()->create([
            'user_id' => $this->user->id,
            'is_default' => false
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->deleteJson("/api/v1/user/master-data/transaction-category/{$category->id}");

        $response->assertStatus(200);
        
        $this->assertDatabaseMissing('transaction_categories', [
            'id' => $category->id
        ]);
    }
}
