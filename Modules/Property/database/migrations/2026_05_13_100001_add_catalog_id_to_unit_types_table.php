<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('unit_types', function (Blueprint $table) {
            $table->foreignId('catalog_id')
                ->nullable()
                ->after('property_id')
                ->constrained('project_unit_types')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('unit_types', function (Blueprint $table) {
            $table->dropConstrainedForeignId('catalog_id');
        });
    }
};
