<?php

use App\Models\Debt;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create((new Debt())->getTable(), function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('contact_name'); // nama orang / lembaga
            $table->enum('type', ['debt', 'receivable']); // debt = kita utang, receivable = orang lain utang ke kita

            $table->decimal('total_amount', 16, 2); // nilai total
            $table->decimal('paid_amount', 16, 2)->default(0); // nilai yang sudah dibayar

            $table->enum('status', ['unpaid', 'partial', 'paid'])->default('unpaid');

            $table->date('due_date')->nullable();
            $table->text('note')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists((new Debt())->getTable());
    }
};
