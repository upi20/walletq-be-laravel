<?php

use App\Models\AccountCategory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create((new AccountCategory())->getTable(), function (Blueprint $table) {
            $table->id();

            // Jika null → default global
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');

            $table->string('name');         // Nama kategori: Cash, Bank, dll
            $table->boolean('is_default')->default(false); // Penanda default dari sistem

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists((new AccountCategory())->getTable());
    }
};
