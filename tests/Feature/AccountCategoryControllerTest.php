<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\AccountCategory;
use App\Models\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class AccountCategoryControllerTest extends TestCase
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

    /**
     * Test listing categories with various filters and pagination
     */
    public function test_can_list_categories_with_complex_filters()
    {
        // Create multiple categories for testing
        AccountCategory::factory()->count(16)->create([
            'user_id' => $this->user->id,
            'type' => 'bank'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/v1/user/master-data/account-category?search=bank&type=bank&per_page=15&page=2');

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
            ])
            ->assertJson([
                'data' => [
                    'pagination' => [
                        'current_page' => 2,
                        'total' => 16
                    ]
                ]
            ]);
    }

    /**
     * Test creating a category with duplicate name
     */
    public function test_cannot_create_duplicate_category()
    {
        // Create an existing category
        AccountCategory::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Existing Bank',
            'type' => 'bank'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/v1/user/master-data/account-category', [
            'name' => 'Existing Bank',
            'type' => 'bank'
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 422,
                'message' => 'Validation error',
                'errors' => [
                    'name' => ['You already have a category with this name.']
                ]
            ]);
    }

    /**
     * Test updating a category that has associated accounts
     */
    public function test_can_update_category_with_accounts()
    {
        // Create a category
        $category = AccountCategory::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Old Bank Name',
            'type' => 'bank'
        ]);

        // Create some accounts for this category
        Account::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'account_category_id' => $category->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson("/api/v1/user/master-data/account-category/{$category->id}", [
            'name' => 'Updated Bank Name',
            'type' => 'bank'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 200,
                'message' => 'Account category updated successfully',
                'data' => [
                    'name' => 'Updated Bank Name',
                    'type' => 'bank'
                ]
            ]);

        $this->assertDatabaseHas('account_categories', [
            'id' => $category->id,
            'name' => 'Updated Bank Name'
        ]);
    }

    /**
     * Test deleting a default category
     */
    public function test_cannot_delete_default_category()
    {
        $category = AccountCategory::factory()->create([
            'user_id' => $this->user->id,
            'is_default' => true
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->deleteJson("/api/v1/user/master-data/account-category/{$category->id}");

        $response->assertStatus(422)
            ->assertJson([
                'status' => 422,
                'message' => 'Cannot delete default category'
            ]);

        $this->assertDatabaseHas('account_categories', [
            'id' => $category->id
        ]);
    }

    /**
     * Test deleting a category that has associated accounts
     */
    public function test_cannot_delete_category_with_accounts()
    {
        // Create a category
        $category = AccountCategory::factory()->create([
            'user_id' => $this->user->id
        ]);

        // Create an account using this category
        Account::factory()->create([
            'user_id' => $this->user->id,
            'account_category_id' => $category->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->deleteJson("/api/v1/user/master-data/account-category/{$category->id}");

        $response->assertStatus(422)
            ->assertJson([
                'status' => 422,
                'message' => 'Cannot delete category with associated accounts'
            ]);

        $this->assertDatabaseHas('account_categories', [
            'id' => $category->id
        ]);
    }

    /**
     * Test authentication failures
     */
    public function test_authentication_scenarios()
    {
        // Test without token
        $this->getJson('/api/v1/user/master-data/account-category')
            ->assertStatus(401);

        // Test with invalid token
        $this->withHeaders([
            'Authorization' => 'Bearer invalid_token'
        ])->getJson('/api/v1/user/master-data/account-category')
            ->assertStatus(401);

        // Test with malformed token
        $this->withHeaders([
            'Authorization' => 'InvalidFormat token123'
        ])->getJson('/api/v1/user/master-data/account-category')
            ->assertStatus(401);
    }

    /**
     * Test successful category creation
     */
    public function test_can_create_valid_category()
    {
        $categoryData = [
            'name' => 'New Test Category',
            'type' => 'bank'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/v1/user/master-data/account-category', $categoryData);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 201,
                'message' => 'Account category created successfully',
                'data' => [
                    'name' => 'New Test Category',
                    'type' => 'bank',
                    'is_default' => false
                ]
            ]);

        $this->assertDatabaseHas('account_categories', [
            'name' => 'New Test Category',
            'type' => 'bank',
            'user_id' => $this->user->id,
            'is_default' => false
        ]);
    }

    /**
     * Test validation errors for invalid inputs
     */
    public function test_validation_errors()
    {
        $invalidData = [
            'name' => '',  // Empty name
            'type' => 'invalid_type'  // Invalid type
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/v1/user/master-data/account-category', $invalidData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'type']);
    }

    /**
     * Test viewing a single category
     */
    public function test_can_view_single_category()
    {
        $category = AccountCategory::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Test Category',
            'type' => 'bank'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson("/api/v1/user/master-data/account-category/{$category->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 200,
                'message' => 'Account category fetched successfully',
                'data' => [
                    'name' => 'Test Category',
                    'type' => 'bank'
                ]
            ]);
    }

    /**
     * Test viewing a non-existent category
     */
    public function test_cannot_view_nonexistent_category()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson("/api/v1/user/master-data/account-category/99999");

        $response->assertStatus(404)
            ->assertJson([
                'status' => 404,
                'message' => 'Account category not found'
            ]);
    }

    /**
     * Test successful category deletion
     */
    public function test_can_delete_category()
    {
        $category = AccountCategory::factory()->create([
            'user_id' => $this->user->id,
            'is_default' => false
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->deleteJson("/api/v1/user/master-data/account-category/{$category->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 200,
                'message' => 'Account category deleted successfully'
            ]);

        $this->assertDatabaseMissing('account_categories', [
            'id' => $category->id
        ]);
    }

    /**
     * Test updating with invalid data
     */
    public function test_cannot_update_with_invalid_data()
    {
        $category = AccountCategory::factory()->create([
            'user_id' => $this->user->id
        ]);

        $invalidData = [
            'name' => '',  // Empty name
            'type' => 'invalid_type'  // Invalid type
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson("/api/v1/user/master-data/account-category/{$category->id}", $invalidData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'type']);
    }

    /**
     * Test updating a non-existent category
     */
    public function test_cannot_update_nonexistent_category()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson("/api/v1/user/master-data/account-category/99999", [
            'name' => 'Updated Name',
            'type' => 'bank'
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'status' => 404,
                'message' => 'Account category not found'
            ]);
    }

}
