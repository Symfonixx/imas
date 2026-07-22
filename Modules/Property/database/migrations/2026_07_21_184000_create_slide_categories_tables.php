<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slide_categories', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->text('description')->nullable();
            $table->string('slug')->unique();
            $table->enum('status', ['Published', 'Archived'])->default('Published')->index();
            $table->unsignedInteger('position')->default(0)->index();
            $table->timestamps();
        });

        Schema::create('property_slide_category', function (Blueprint $table) {
            $table->foreignId('property_id')
                ->constrained('properties')
                ->cascadeOnDelete();
            $table->foreignId('slide_category_id')
                ->constrained('slide_categories')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['property_id', 'slide_category_id']);
        });

        Schema::create('property_slide_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')
                ->constrained('properties')
                ->cascadeOnDelete();
            $table->foreignId('slide_category_id')
                ->constrained('slide_categories')
                ->cascadeOnDelete();
            $table->enum('type', ['image', 'video']);
            $table->string('path');
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();

            $table->index(['property_id', 'slide_category_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_slide_media');
        Schema::dropIfExists('property_slide_category');
        Schema::dropIfExists('slide_categories');
    }
};
