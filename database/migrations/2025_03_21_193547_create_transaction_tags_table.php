<?php

use App\Models\TransactionTag;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create((new TransactionTag())->getTable(), function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->nullOnDelete();
            $table->foreignId('tag_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists((new TransactionTag())->getTable());
    }
};
