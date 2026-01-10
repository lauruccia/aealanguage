<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->decimal('course_price_eur', 10, 2)->nullable()->after('status');
            $table->decimal('registration_fee_eur', 10, 2)->nullable()->after('course_price_eur');
        });
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropColumn(['course_price_eur', 'registration_fee_eur']);
        });
    }
};
