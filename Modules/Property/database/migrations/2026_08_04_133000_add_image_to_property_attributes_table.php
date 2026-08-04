<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('property_attributes', function (Blueprint $table) {
            $table->string('image')->nullable()->after('help_text');
        });
    }

    public function down(): void
    {
        Schema::table('property_attributes', function (Blueprint $table) {
            $table->dropColumn('image');
        });
    }
};
