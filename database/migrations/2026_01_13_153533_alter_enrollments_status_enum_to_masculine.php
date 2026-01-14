<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1) ENUM "ponte": consente sia femminile che maschile
        DB::statement("
            ALTER TABLE enrollments
            MODIFY status ENUM(
                'attiva','conclusa','annullata','sospesa',
                'attivo','concluso','annullato','sospeso'
            )
            NOT NULL
            DEFAULT 'attiva'
        ");

        // 2) Conversione dati esistenti (ora possibile)
        DB::statement("UPDATE enrollments SET status = 'attivo'    WHERE status = 'attiva'");
        DB::statement("UPDATE enrollments SET status = 'concluso'  WHERE status = 'conclusa'");
        DB::statement("UPDATE enrollments SET status = 'annullato' WHERE status = 'annullata'");
        DB::statement("UPDATE enrollments SET status = 'sospeso'   WHERE status = 'sospesa'");

        // 3) ENUM definitivo solo maschile
        DB::statement("
            ALTER TABLE enrollments
            MODIFY status ENUM('attivo','concluso','annullato','sospeso')
            NOT NULL
            DEFAULT 'attivo'
        ");
    }

    public function down(): void
    {
        // 1) ENUM "ponte" anche in rollback
        DB::statement("
            ALTER TABLE enrollments
            MODIFY status ENUM(
                'attiva','conclusa','annullata','sospesa',
                'attivo','concluso','annullato','sospeso'
            )
            NOT NULL
            DEFAULT 'attivo'
        ");

        // 2) Torna al femminile
        DB::statement("UPDATE enrollments SET status = 'attiva'    WHERE status = 'attivo'");
        DB::statement("UPDATE enrollments SET status = 'conclusa'  WHERE status = 'concluso'");
        DB::statement("UPDATE enrollments SET status = 'annullata' WHERE status = 'annullato'");
        DB::statement("UPDATE enrollments SET status = 'sospesa'   WHERE status = 'sospeso'");

        // 3) ENUM definitivo femminile
        DB::statement("
            ALTER TABLE enrollments
            MODIFY status ENUM('attiva','conclusa','annullata','sospesa')
            NOT NULL
            DEFAULT 'attiva'
        ");
    }
};
