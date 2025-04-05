<?php

use App\Models\ImportTransaction;
use App\Models\Transaction;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create((new Transaction())->getTable(), function (Blueprint $table) {
            $table->id();

            $table->foreignId('import_id')->nullable()->constrained((new ImportTransaction())->getTable(), 'id')->nullOnDelete()->default(null);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('account_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('transaction_category_id')->nullable()->constrained()->nullOnDelete();

            $table->enum('type', ['income', 'expense']);
            $table->decimal('amount', 16, 2);
            $table->dateTime('date');
            $table->text('note')->nullable();

            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
        
            $table->enum('flag', [
                'normal',
                'transfer_in',
                'transfer_out',
                'debt_payment',
                'debt_collect',
                'initial_balance',
            ])->default('normal');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists((new Transaction())->getTable());
    }
};
