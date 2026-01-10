<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            if (!Schema::hasColumn('enrollments', 'payment_plan')) {
                $table->string('payment_plan', 30)->nullable()->after('registration_fee_eur');
            }

            if (!Schema::hasColumn('enrollments', 'installments_count')) {
                $table->unsignedTinyInteger('installments_count')->default(1)->after('payment_plan');
            }

            if (!Schema::hasColumn('enrollments', 'first_installment_due_date')) {
                $table->date('first_installment_due_date')->nullable()->after('installments_count');
            }

            if (!Schema::hasColumn('enrollments', 'enrolled_at')) {
                $table->date('enrolled_at')->nullable()->after('first_installment_due_date');
            }

            if (!Schema::hasColumn('enrollments', 'starts_at')) {
                $table->date('starts_at')->nullable()->after('enrolled_at');
            }

            if (!Schema::hasColumn('enrollments', 'ends_at')) {
                $table->date('ends_at')->nullable()->after('starts_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            // In down non possiamo sapere cosa esisteva prima.
            // Per sicurezza, droppiamo solo se esiste.
            $cols = [
                'payment_plan',
                'installments_count',
                'first_installment_due_date',
                'enrolled_at',
                'starts_at',
                'ends_at',
            ];

            foreach ($cols as $col) {
                if (Schema::hasColumn('enrollments', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
