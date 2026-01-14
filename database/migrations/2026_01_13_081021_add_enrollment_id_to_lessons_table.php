<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
        if (!Schema::hasColumn('lessons', 'enrollment_id')) {
            $table->foreignId('enrollment_id')
                ->nullable()
                ->after('id')
                ->constrained('enrollments')
                ->nullOnDelete();

            $table->index(['enrollment_id', 'start_at']);
        }
        });
    }
};
