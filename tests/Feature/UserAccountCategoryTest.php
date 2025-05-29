<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\AccountCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserAccountCategoryTest extends TestCase
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

    public function test_can_list_account_categories_with_pagination()
    {
        // Create some account categories
        AccountCategory::factory()->count(15)->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/v1/user/master-data/account-category?per_page=10');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'categories' => [
                            '*' => [
                                'id',
                                'name',
                                'type',
                                'is_default',
                                'created_at',
                                'updated_at'
                            ]
                        ],
                        'pagination' => [
                            'current_page',
                            'per_page',
                            'total',
                            'last_page'
                        ]
                    ]
                ]);
        
        // Check that we're getting 10 items per page
        $this->assertCount(10, $response->json()['data']['categories']);
        // Check that total is 15
        $this->assertEquals(15, $response->json()['data']['pagination']['total']);
    }

    public function test_can_search_account_categories()
    {
        // Create categories with specific names
        AccountCategory::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Test Bank Account'
        ]);
        AccountCategory::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Another Category'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/v1/user/master-data/account-category?search=Bank');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json()['data']['categories']);
        $this->assertEquals('Test Bank Account', $response->json()['data']['categories'][0]['name']);
    }

    public function test_can_filter_account_categories_by_type()
    {
        AccountCategory::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'bank'
        ]);
        AccountCategory::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'cash'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/v1/user/master-data/account-category?type=bank');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json()['data']['categories']);
        $this->assertEquals('bank', $response->json()['data']['categories'][0]['type']);
    }

    public function test_can_create_account_category()
    {
        $categoryData = [
            'name' => 'New Test Category',
            'type' => 'bank'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/v1/user/master-data/account-category', $categoryData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'id',
                        'name',
                        'type',
                        'is_default',
                        'created_at',
                        'updated_at'
                    ]
                ]);

        $this->assertDatabaseHas('account_categories', [
            'name' => 'New Test Category',
            'type' => 'bank',
            'user_id' => $this->user->id
        ]);
    }

    public function test_cannot_create_duplicate_category_names()
    {
        // Create an existing category
        AccountCategory::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Test Category'
        ]);

        $categoryData = [
            'name' => 'Test Category',
            'type' => 'bank'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/v1/user/master-data/account-category', $categoryData);

        $response->assertStatus(422);
    }

    public function test_cannot_modify_default_category()
    {
        $category = AccountCategory::factory()->create([
            'user_id' => $this->user->id,
            'is_default' => true
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson("/api/v1/user/master-data/account-category/{$category->id}", [
            'name' => 'Updated Name',
            'type' => 'bank'
        ]);

        $response->assertStatus(422)
                ->assertJson([
                    'status' => 422,
                    'message' => 'Cannot modify default category'
                ]);
    }

    public function test_cannot_delete_category_with_accounts()
    {
        $category = AccountCategory::factory()->create([
            'user_id' => $this->user->id
        ]);

        // Create an account using this category
        $account = $this->user->accounts()->create([
            'name' => 'Test Account',
            'account_category_id' => $category->id,
            'initial_balance' => 0,
            'current_balance' => 0
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->deleteJson("/api/v1/user/master-data/account-category/{$category->id}");

        $response->assertStatus(422)
                ->assertJson([
                    'status' => 422,
                    'message' => 'Cannot delete category with associated accounts'
                ]);
    }
}
