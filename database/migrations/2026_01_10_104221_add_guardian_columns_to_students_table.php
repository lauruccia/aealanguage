<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'guardian_role')) {
                $table->string('guardian_role')->nullable()->after('is_minor'); // padre/madre/altro
            }
            if (!Schema::hasColumn('students', 'guardian_name')) {
                $table->string('guardian_name')->nullable()->after('guardian_role');
            }
            if (!Schema::hasColumn('students', 'guardian_email')) {
                $table->string('guardian_email')->nullable()->after('guardian_name');
            }
            if (!Schema::hasColumn('students', 'guardian_phone')) {
                $table->string('guardian_phone')->nullable()->after('guardian_email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $cols = [];
            foreach (['guardian_role','guardian_name','guardian_email','guardian_phone'] as $c) {
                if (Schema::hasColumn('students', $c)) $cols[] = $c;
            }
            if (!empty($cols)) $table->dropColumn($cols);
        });
    }
};
