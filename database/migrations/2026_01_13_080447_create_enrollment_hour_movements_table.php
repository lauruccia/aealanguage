<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('enrollment_hour_movements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('enrollment_id')
                ->constrained('enrollments')
                ->cascadeOnDelete();

            // minuti: positivo = credito, negativo = debito
            $table->integer('minutes');

            // purchase | bonus | adjustment | lesson
            $table->string('type', 30);

            // collega consumo a una lezione, se vuoi tracciamento perfetto
            $table->foreignId('lesson_id')
                ->nullable()
                ->constrained('lessons')
                ->nullOnDelete();

            $table->text('note')->nullable();

            // data “operativa” del movimento (utile per report)
            $table->timestamp('occurred_at')->useCurrent();

            $table->timestamps();

            $table->index(['enrollment_id', 'type']);
            $table->index(['lesson_id']);
            $table->index(['occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollment_hour_movements');
    }
};
