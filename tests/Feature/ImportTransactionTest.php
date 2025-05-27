<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Account;
use App\Models\AccountCategory;
use App\Models\Transaction;
use App\Models\TransactionCategory;
use App\Models\ImportTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class ImportTransactionTest extends TestCase
{
	use RefreshDatabase;

	protected $user;
	protected $token;
	protected $accountCategory;

	protected function setUp(): void
	{
		parent::setUp();

		// Create user and default categories
		$this->user = User::factory()->create([
			'email' => 'test@example.com',
			'password' => bcrypt('password123'),
		]);

		// Create default account category
		$this->accountCategory = AccountCategory::create([
			'name' => 'Cash',
			'user_id' => null, // Global category
			'is_default' => true
		]);

		// Create essential default categories
		TransactionCategory::create([
			'name' => Transaction::CATEGORY_INITIAL_BALANCE,
			'type' => Transaction::TYPE_INCOME,
			'user_id' => null, // Global category
			'is_default' => true
		]);

		// Get JWT token
		$credentials = [
			'email' => 'test@example.com',
			'password' => 'password123'
		];
		$this->token = auth('api')->attempt($credentials);
	}

	private function createTestExcelFile($data = null)
	{
		if ($data === null) {
			// Default test data with format matching CSV
			$data = [
				['Saldo Awal', 'Bank BCA', '1000000', '2024-01-01 00:00', 'Initial Balance', '+'],
				['Gaji', 'Bank BCA', '5000000', '2024-01-02 00:00', 'Gaji Januari', '+'],
				['Makan', 'Bank BCA', '50000', '2024-01-03 00:00', 'Makan Siang', '-']
			];
		}

		$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();

		// Add headers
		$headers = ['Kategori', 'Rekening', 'Jumlah', 'Tanggal', 'Catatan', 'Tipe'];
		foreach ($headers as $col => $header) {
			$sheet->setCellValueByColumnAndRow($col + 1, 1, $header);
		}

		// Add data
		foreach ($data as $row => $rowData) {
			foreach ($rowData as $col => $value) {
				$sheet->setCellValueByColumnAndRow($col + 1, $row + 2, $value);
			}
		}

		// Save to temp file
		$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
		$fileName = 'test_import.xls';
		$path = sys_get_temp_dir() . '/' . $fileName;
		$writer->save($path);

		return new UploadedFile(
			$path,
			$fileName,
			'application/vnd.ms-excel',
			null,
			true
		);
	}

	public function test_successful_import()
	{
		Storage::fake('public');

		// Create test Excel file with valid data
		$file = $this->createTestExcelFile();

		$response = $this->withHeaders([
			'Authorization' => 'Bearer ' . $this->token
		])->post('/api/v1/user/transaction/import', [
			'file' => $file
		]);

		$response->assertStatus(200)
			->assertJsonStructure([
				'status',
				'message',
				'data' => [
					'*' => [
						'id',
						'import_id',
						'user_id',
						'account_id',
						'transaction_category_id',
						'type',
						'amount',
						'date',
						'note'
					]
				]
			]);

		// Verify import record was created
		$this->assertDatabaseHas('import_transactions', [
			'file' => 'import/excel/transaction/' . date('YmdHis-') . 'test_import.xls'
		]);

		// Verify transactions were created
		$importTransaction = ImportTransaction::first();
		$this->assertNotNull($importTransaction);

		$transactions = Transaction::where('import_id', $importTransaction->id)->get();
		$this->assertGreaterThan(0, $transactions->count());

		// Verify account was created
		$accounts = Account::where('user_id', $this->user->id)->get();
		$this->assertGreaterThan(0, $accounts->count());
	}

	public function test_invalid_file_format()
	{
		Storage::fake('public');

		$file = UploadedFile::fake()->create('invalid.txt', 100);

		$response = $this->withHeaders([
			'Authorization' => 'Bearer ' . $this->token
		])->post('/api/v1/user/transaction/import', [
			'file' => $file
		]);

		$response->assertStatus(422);
	}

	public function test_insufficient_balance_error()
	{
		Storage::fake('public');

		// Create account with 0 balance
		$account = Account::factory()->create([
			'user_id' => $this->user->id,
			'name' => 'Test Account',
			'initial_balance' => 0,
			'current_balance' => 0
		]);

		// Create a test Excel file with expense transaction
		$file = $this->createTestExcelFile([
			['Makan', $account->name, 100000, '2024-01-01', 'Test expense', '-']
		]);

		$response = $this->withHeaders([
			'Authorization' => 'Bearer ' . $this->token
		])->post('/api/v1/user/transaction/import', [
			'file' => $file
		]);

		$response->assertStatus(400)
			->assertJsonFragment([
				'message' => "Insufficient balance in account {$account->name} for transaction of 100000"
			]);
	}

	public function test_csv_import()
	{
		Storage::fake('public');

		// Create essential categories if needed
		if (!TransactionCategory::where('name', 'Kost')->where('user_id', $this->user->id)->exists()) {
			TransactionCategory::create([
				'name' => 'Kost',
				'type' => 'expense',
				'user_id' => $this->user->id
			]);
		}

		// Copy CSV file from public directory
		$sourcePath = public_path('contoh-file-import.csv');

		$file = new UploadedFile(
			$sourcePath,
			'contoh-file-import.csv',
			'text/csv',
			null,
			true
		);

		$response = $this->withHeaders([
			'Authorization' => 'Bearer ' . $this->token
		])->post('/api/v1/user/transaction/import', [
			'file' => $file
		]);

		// Output response content for debugging
		if ($response->status() !== 200) {
			var_dump($response->content());
		}

		$response->assertStatus(200)
			->assertJsonStructure([
				'status',
				'message',
				'data' => [
					'*' => [
						'id',
						'import_id',
						'user_id',
						'account_id',
						'transaction_category_id',
						'type',
						'amount',
						'date',
						'note'
					]
				]
			]);

		// Verify import record was created
		$importTransaction = ImportTransaction::first();
		$this->assertNotNull($importTransaction);

		// Verify transactions were created
		$transactions = Transaction::where('import_id', $importTransaction->id)->get();
		$this->assertGreaterThan(0, $transactions->count());

		// Verify first transaction matches expected format
		$firstTransaction = $transactions->first();
		$this->assertEquals('Kost', TransactionCategory::find($firstTransaction->transaction_category_id)->name);
		$this->assertEquals('DOMPET', Account::find($firstTransaction->account_id)->name);
		$this->assertEquals('expense', $firstTransaction->type);
		$this->assertEquals(650000, $firstTransaction->amount); // Amount without comma
		$this->assertEquals('2025-05-28 19:20:00', $firstTransaction->date); // Full datetime
	}
}
