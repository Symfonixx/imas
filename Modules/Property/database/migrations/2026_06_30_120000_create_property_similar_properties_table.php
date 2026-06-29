<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_similar_properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->foreignId('similar_property_id')->constrained('properties')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['property_id', 'similar_property_id'], 'property_similar_unique');
            $table->index('property_id');
            $table->index('similar_property_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_similar_properties');
    }
};
