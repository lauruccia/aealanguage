<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->string('contract_type')->nullable()->after('phone');
            $table->decimal('gross_hourly_rate', 10, 2)->nullable()->after('contract_type');

            $table->string('pec')->nullable()->after('gross_hourly_rate');
            $table->string('iban', 34)->nullable()->after('pec');

            $table->string('billing_mode')->nullable()->after('iban');
            $table->decimal('vat_percentage', 5, 2)->nullable()->after('billing_mode');

            $table->string('cv_path')->nullable()->after('vat_percentage');
            $table->string('id_document_path')->nullable()->after('cv_path');
        });
    }

    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn([
                'contract_type',
                'gross_hourly_rate',
                'pec',
                'iban',
                'billing_mode',
                'vat_percentage',
                'cv_path',
                'id_document_path',
            ]);
        });
    }
};
