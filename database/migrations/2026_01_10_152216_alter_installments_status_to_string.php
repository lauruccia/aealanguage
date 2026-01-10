<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Prima: rendi la colonna una stringa (così accetta 'da_pagare')
        Schema::table('installments', function (Blueprint $table) {
            $table->string('status', 50)->change();
        });

        // 2) Poi: aggiorna i valori (con virgolette corrette)
        DB::table('installments')
            ->where('status', 'pending')
            ->update(['status' => 'da_pagare']);

        DB::table('installments')
            ->where('status', 'paid')
            ->update(['status' => 'pagata']);
    }

    public function down(): void
    {
        // Se vuoi tornare indietro, scegli una strategia coerente.
        // Esempio: riconverti in enum (se prima era enum) o in string più corta.
        // Qui metto un rollback "sicuro" a string per non spaccare dati.
        Schema::table('installments', function (Blueprint $table) {
            $table->string('status', 50)->change();
        });
    }
};
