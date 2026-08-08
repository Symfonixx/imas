<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media_folders', function (Blueprint $table) {
            $table->dropUnique(['name']);

            $table->foreignId('parent_id')
                ->nullable()
                ->after('id')
                ->constrained('media_folders')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('media_folders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_id');
            $table->unique('name');
        });
    }
};
