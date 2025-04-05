<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create((new Setting())->getTable(), function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('key');   // contoh: 'currency'
            $table->string('value'); // contoh: 'IDR'

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists((new Setting())->getTable());
    }
};
