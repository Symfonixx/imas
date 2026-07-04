<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_forms', function (Blueprint $table) {
            $table->string('source_url', 2048)->nullable()->after('subject');
            $table->string('source_page')->nullable()->after('source_url');
        });
    }

    public function down(): void
    {
        Schema::table('contact_forms', function (Blueprint $table) {
            $table->dropColumn(['source_url', 'source_page']);
        });
    }
};
