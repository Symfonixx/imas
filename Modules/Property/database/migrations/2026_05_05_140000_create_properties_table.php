<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('thumbnail')->nullable();
            $table->string('project_code', 128)->unique();
            $table->json('title');
            $table->json('project_name');
            $table->json('overview');

            $table->foreignId('location_id')
                ->constrained('locations')
                ->cascadeOnDelete();

            $table->foreignId('property_type_id')
                ->nullable()
                ->constrained('property_types')
                ->nullOnDelete();

            $table->decimal('price', 14, 2)->default(0);
            $table->decimal('min_area', 14, 2)->nullable();
            $table->decimal('max_area', 14, 2)->nullable();

            $table->boolean('is_sold_out')->default(false);
            $table->boolean('is_recommended')->default(false);
            $table->boolean('is_citizenship_eligible')->default(false);
            $table->boolean('is_featured')->default(false);

            $table->json('why_to_buy');
            $table->json('facilities')->nullable();
            $table->json('content');
            $table->string('youtube_video_url')->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();

            $table->enum('status', ['Published', 'Archived'])->default('Published');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
