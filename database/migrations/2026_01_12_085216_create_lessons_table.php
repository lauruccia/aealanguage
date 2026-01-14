<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();

            $table->foreignId('enrollment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();

            $table->foreignId('teacher_id')->nullable()->constrained()->nullOnDelete();

            // numero progressivo lezione per quella iscrizione (1..N)
            $table->unsignedInteger('lesson_number');

            // quando è pianificata (data+ora)
            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();

            // durata effettiva (es. 60 minuti). La useremo per conteggio ore docente.
            $table->unsignedInteger('duration_minutes')->default(60);

            // stato: programmata / svolta / annullata_da_recuperare / annullata_fruita
            $table->string('status')->default('scheduled');

            // gestione annullamenti
            $table->dateTime('cancelled_at')->nullable();
            $table->string('cancel_reason')->nullable();

            // note interne (segreteria)
            $table->text('notes')->nullable();

            $table->timestamps();

            // univoco: per ogni iscrizione non possono esistere due lezioni con stesso numero
            $table->unique(['enrollment_id', 'lesson_number']);
            $table->index(['teacher_id', 'starts_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
