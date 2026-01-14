<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            if (!Schema::hasColumn('lessons', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('lessons', 'cancel_reason')) {
                $table->text('cancel_reason')->nullable()->after('cancelled_at');
            }
            if (!Schema::hasColumn('lessons', 'previous_start_at')) {
                $table->timestamp('previous_start_at')->nullable()->after('cancel_reason');
            }
            if (!Schema::hasColumn('lessons', 'previous_end_at')) {
                $table->timestamp('previous_end_at')->nullable()->after('previous_start_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            if (Schema::hasColumn('lessons', 'previous_end_at')) $table->dropColumn('previous_end_at');
            if (Schema::hasColumn('lessons', 'previous_start_at')) $table->dropColumn('previous_start_at');
            if (Schema::hasColumn('lessons', 'cancel_reason')) $table->dropColumn('cancel_reason');
            if (Schema::hasColumn('lessons', 'cancelled_at')) $table->dropColumn('cancelled_at');
        });
    }
};
