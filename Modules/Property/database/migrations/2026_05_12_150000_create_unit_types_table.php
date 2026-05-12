<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unit_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')
                ->constrained('properties')
                ->cascadeOnDelete();
            $table->string('name');
            $table->decimal('min_area', 14, 2)->nullable();
            $table->decimal('max_area', 14, 2)->nullable();
            $table->decimal('price', 14, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_types');
    }
};
