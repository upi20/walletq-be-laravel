<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Account;
use App\Models\AccountCategory;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class AccountTest extends TestCase
{
	use RefreshDatabase;

	protected $user;
	protected $token;
	protected $accountCategory;

	protected function setUp(): void
	{
		parent::setUp();

		$this->user = User::factory()->create();
		$this->token = JWTAuth::fromUser($this->user);
		$this->accountCategory = AccountCategory::factory()->create([
			'user_id' => $this->user->id
		]);
	}

	public function test_can_get_accounts()
	{
		Account::factory()->count(3)->create([
			'user_id' => $this->user->id,
			'account_category_id' => $this->accountCategory->id
		]);

		$response = $this->withHeaders([
			'Authorization' => 'Bearer ' . $this->token
		])->getJson('/api/v1/user/master-data/account');

		$response->assertStatus(200)
			->assertJsonStructure([
				'status',
				'message',
				'data' => [
					'balance',
					'accounts' => [
						'*' => [
							'id',
							'name',
							'account_category_id',
							'initial_balance',
							'current_balance',
							'created_at',
							'updated_at',
							'category'
						]
					]
				]
			]);
	}

	public function test_can_create_account()
	{
		$accountData = [
			'name' => 'Test Account',
			'account_category_id' => $this->accountCategory->id,
			'initial_balance' => 1000000
		];

		$response = $this->withHeaders([
			'Authorization' => 'Bearer ' . $this->token
		])->postJson('/api/v1/user/master-data/account', $accountData);

		$response->assertStatus(201)
			->assertJsonStructure([
				'status',
				'message',
				'data' => [
					'id',
					'name',
					'account_category_id',
					'initial_balance',
					'current_balance',
					'created_at',
					'updated_at'
				]
			]);

		$this->assertDatabaseHas('accounts', [
			'name' => 'Test Account',
			'initial_balance' => 1000000,
			'current_balance' => 1000000,
			'user_id' => $this->user->id
		]);

		// Check if initial balance transaction was created
		$this->assertDatabaseHas('transactions', [
			'user_id' => $this->user->id,
			'amount' => 1000000,
			'type' => Transaction::TYPE_INCOME,
			'flag' => Transaction::FLAG_INITIAL_BALANCE
		]);
	}

	public function test_can_view_account_details()
	{
		$account = Account::factory()->create([
			'user_id' => $this->user->id,
			'account_category_id' => $this->accountCategory->id
		]);

		$response = $this->withHeaders([
			'Authorization' => 'Bearer ' . $this->token
		])->getJson("/api/v1/user/master-data/account/{$account->id}");

		$response->assertStatus(200)
			->assertJsonStructure([
				'status',
				'message',
				'data' => [
					'id',
					'name',
					'account_category_id',
					'initial_balance',
					'current_balance',
					'created_at',
					'updated_at',
					'category'
				]
			]);
	}

	public function test_can_update_account()
	{
		$account = Account::factory()->create([
			'user_id' => $this->user->id,
			'account_category_id' => $this->accountCategory->id
		]);

		$updateData = [
			'name' => 'Updated Account Name',
			'account_category_id' => $this->accountCategory->id
		];

		$response = $this->withHeaders([
			'Authorization' => 'Bearer ' . $this->token
		])->putJson("/api/v1/user/master-data/account/{$account->id}", $updateData);

		$response->assertStatus(200);

		$this->assertDatabaseHas('accounts', [
			'id' => $account->id,
			'name' => 'Updated Account Name'
		]);
	}

	public function test_cannot_delete_account_with_transactions()
	{
		$account = Account::factory()->create([
			'user_id' => $this->user->id,
			'account_category_id' => $this->accountCategory->id
		]);

		// Create a transaction for this account
		Transaction::factory()->create([
			'user_id' => $this->user->id,
			'account_id' => $account->id
		]);

		$response = $this->withHeaders([
			'Authorization' => 'Bearer ' . $this->token
		])->deleteJson("/api/v1/user/master-data/account/{$account->id}");

		$response->assertStatus(422);

		$this->assertDatabaseHas('accounts', [
			'id' => $account->id
		]);
	}
	public function test_can_delete_unused_account()
	{
		$account = Account::factory()->create([
			'user_id' => $this->user->id,
			'account_category_id' => $this->accountCategory->id,
			'initial_balance' => 0,  // Set initial balance to 0 to prevent initial balance transaction
			'current_balance' => 0
		]);

		$response = $this->withHeaders([
			'Authorization' => 'Bearer ' . $this->token
		])->deleteJson("/api/v1/user/master-data/account/{$account->id}");

		$response->assertStatus(200);

		$this->assertDatabaseMissing('accounts', [
			'id' => $account->id
		]);
	}

	public function test_cannot_access_other_user_account()
	{
		$otherUser = User::factory()->create();
		$account = Account::factory()->create([
			'user_id' => $otherUser->id,
			'account_category_id' => $this->accountCategory->id
		]);

		$response = $this->withHeaders([
			'Authorization' => 'Bearer ' . $this->token
		])->getJson("/api/v1/user/master-data/account/{$account->id}");

		$response->assertStatus(404);
	}
}
