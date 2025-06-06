<?php

namespace Database\Seeders;

use App\Imports\Transactions;
use App\Models\ImportTransaction;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->call([
            AccountCategorySeeder::class,
            TransactionCategorySeeder::class,
        ]);

        $this->customSeeder();
    }


    public function customSeeder()
    {
        $authController = new \App\Http\Controllers\API\AuthController();
        $request = new \Illuminate\Http\Request();
        $request->merge([
            'name' => 'Isep Lutpinur',
            'email' => 'iseplutpinur7@gmail.com',
            'password' => '12345678',
            'password_confirmation' => '12345678',
        ]);
        $responseRegister = $authController->register($request);
        // $token = json_decode($responseRegister->getContent())->data->token; // bearer token

        $user = User::where('email', 'iseplutpinur7@gmail.com')->first();
        DB::beginTransaction();

        // simpan data upload ke database
        $import = new ImportTransaction();
        $import->file = 'contoh-file-import.xls';
        $import->save();

        Excel::import(new Transactions($import->id), public_path('contoh-file-import.xls'));

        User::refreshBalance($user->id);
        DB::commit();
    }
}
