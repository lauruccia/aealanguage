<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL: per cambiare enum serve una query raw
        DB::statement("ALTER TABLE installments MODIFY status ENUM('pending','partial','paid','overdue') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        // Se vuoi tornare indietro, metti qui la vecchia enum (se la conosci).
        // Per ora lasciamo vuoto per non rompere.
    }
};
