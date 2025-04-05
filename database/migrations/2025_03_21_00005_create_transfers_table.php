<?php

use App\Models\Transfer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create((new Transfer())->getTable(), function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->nullOnDelete();
            $table->foreignId('from_account_id')->constrained('accounts')->nullOnDelete();
            $table->foreignId('to_account_id')->constrained('accounts')->nullOnDelete();

            $table->decimal('amount', 16, 2);
            $table->date('date');
            $table->text('note')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists((new Transfer())->getTable());
    }
};
