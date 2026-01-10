<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // Dati anagrafici extra
            $table->string('birth_place')->nullable()->after('birth_date');
            $table->string('birth_country')->nullable()->after('birth_place');

            // Dati fiscali
            $table->string('tax_code', 32)->nullable()->after('birth_country');        // Codice Fiscale
            $table->string('vat_number', 32)->nullable()->after('tax_code');          // Partita IVA

            // Indirizzo
            $table->string('address_line')->nullable()->after('vat_number');          // Via/Piazza + numero
            $table->string('postal_code', 16)->nullable()->after('address_line');    // CAP
            $table->string('city')->nullable()->after('postal_code');                // Città
            $table->string('province', 32)->nullable()->after('city');               // Provincia
            $table->string('country')->nullable()->after('province');                // Nazione (residenza)
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'birth_place',
                'birth_country',
                'tax_code',
                'vat_number',
                'address_line',
                'postal_code',
                'city',
                'province',
                'country',
            ]);
        });
    }
};
