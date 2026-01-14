<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            if (!Schema::hasColumn('enrollments', 'purchased_minutes')) {
                $table->unsignedInteger('purchased_minutes')
                    ->default(0)
                    ->after('lesson_duration_minutes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            if (Schema::hasColumn('enrollments', 'purchased_minutes')) {
                $table->dropColumn('purchased_minutes');
            }
        });
    }
};
