<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            // Qui NON usiamo dropConstrainedForeignId perché nel tuo DB la FK non esiste.
            if (Schema::hasColumn('enrollments', 'language_id')) {
                $table->dropColumn('language_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            // Ripristino "soft" (senza FK) per non dipendere dalla tabella languages
            if (! Schema::hasColumn('enrollments', 'language_id')) {
                $table->unsignedBigInteger('language_id')->nullable()->after('course_id');
            }
        });
    }
};
