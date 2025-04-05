<?php

use App\Models\Account;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create((new Account())->getTable(), function (Blueprint $table) {
            $table->id();

            // Relasi ke user
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Kategori akun
            $table->foreignId('account_category_id')->nullable()->constrained()->nullOnDelete();

            $table->string('name'); // contoh: BCA, Dompet, OVO
            $table->string('type')->nullable(); // misal: 'tabungan', 'utama'
            $table->text('note')->nullable();

            $table->decimal('initial_balance', 16, 2)->default(0);
            $table->decimal('current_balance', 16, 2)->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists((new Account())->getTable());
    }
};
