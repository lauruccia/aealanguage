<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->string('google_event_id')->nullable()->after('ends_at');
            $table->text('google_meet_url')->nullable()->after('google_event_id');
            $table->text('google_html_link')->nullable()->after('google_meet_url');
        });
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropColumn(['google_event_id', 'google_meet_url', 'google_html_link']);
        });
    }
};
