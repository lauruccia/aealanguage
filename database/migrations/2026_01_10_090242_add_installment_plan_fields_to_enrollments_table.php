<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->unsignedTinyInteger('installments_count')->default(1)->after('course_id');
            $table->date('first_installment_due_date')->nullable()->after('ends_at');
        });
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropColumn(['installments_count', 'first_installment_due_date']);
        });
    }
};
