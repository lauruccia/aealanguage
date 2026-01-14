<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $table) {

            // Anagrafica estesa
            $table->date('birth_date')->nullable()->after('phone');
            $table->string('birth_place')->nullable()->after('birth_date');
            $table->string('birth_country')->nullable()->after('birth_place');

            // Dati fiscali
            $table->string('vat_number')->nullable()->after('birth_country');
            $table->string('tax_code')->nullable()->after('vat_number');

            // Residenza
            $table->string('address')->nullable()->after('tax_code');
            $table->string('postal_code', 10)->nullable()->after('address');
            $table->string('city')->nullable()->after('postal_code');
            $table->string('province', 10)->nullable()->after('city');
            $table->string('residence_country')->nullable()->after('province');
        });
    }

    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn([
                'birth_date',
                'birth_place',
                'birth_country',
                'vat_number',
                'tax_code',
                'address',
                'postal_code',
                'city',
                'province',
                'residence_country',
            ]);
        });
    }
};
