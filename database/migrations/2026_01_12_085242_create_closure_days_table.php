<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('closure_days', function (Blueprint $table) {
            $table->id();

            // giorno di chiusura (es. 2026-01-06)
            $table->date('date')->unique();

            // opzionale: motivazione (festività, chiusura straordinaria, ecc.)
            $table->string('reason')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('closure_days');
    }
};
