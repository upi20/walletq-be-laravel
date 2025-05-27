<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\AccountCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class AccountCategoryTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a user
        $this->user = User::factory()->create();
        $this->token = JWTAuth::fromUser($this->user);
    }

    public function test_can_get_account_categories()
    {
        // Create some account categories
        AccountCategory::factory()->count(3)->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/v1/admin/master-data/account-categories');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'type',
                            'created_at',
                            'updated_at'
                        ]
                    ]
                ]);
    }

    public function test_can_create_account_category()
    {
        $categoryData = [
            'name' => 'Test Category',
            'type' => 'cash'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/v1/admin/master-data/account-categories', $categoryData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'id',
                        'name',
                        'type',
                        'created_at',
                        'updated_at'
                    ]
                ]);

        $this->assertDatabaseHas('account_categories', [
            'name' => 'Test Category',
            'type' => 'cash',
            'user_id' => $this->user->id
        ]);
    }

    public function test_can_update_account_category()
    {
        $category = AccountCategory::factory()->create([
            'user_id' => $this->user->id
        ]);

        $updateData = [
            'name' => 'Updated Category',
            'type' => 'bank'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson("/api/v1/admin/master-data/account-categories/{$category->id}", $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('account_categories', [
            'id' => $category->id,
            'name' => 'Updated Category',
            'type' => 'bank'
        ]);
    }

    public function test_can_delete_account_category()
    {
        $category = AccountCategory::factory()->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->deleteJson("/api/v1/admin/master-data/account-categories/{$category->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('account_categories', [
            'id' => $category->id
        ]);
    }

    public function test_cannot_create_duplicate_category()
    {
        // Create initial category
        AccountCategory::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Test Category'
        ]);

        // Try to create duplicate
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/v1/admin/master-data/account-categories', [
            'name' => 'Test Category',
            'type' => 'cash'
        ]);

        $response->assertStatus(422);
    }
}
