<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            // giorno settimana: 1=Lun ... 7=Dom (più facile per UI)
            $table->unsignedTinyInteger('weekly_day')->nullable()->after('starts_at');

            // ora lezione: "17:00"
            $table->string('weekly_time', 5)->nullable()->after('weekly_day');

            // durata default lezione
            $table->unsignedSmallInteger('lesson_duration_minutes')->default(60)->after('weekly_time');

            // docente di default (poi ogni lezione può cambiare)
            $table->foreignId('default_teacher_id')
                ->nullable()
                ->after('lesson_duration_minutes')
                ->constrained('teachers')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('default_teacher_id');
            $table->dropColumn(['weekly_day', 'weekly_time', 'lesson_duration_minutes']);
        });
    }
};
