<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->string('client');
            $table->string('avatar')->nullable();
            $table->json('position')->nullable();
            $table->string('link')->nullable();
            $table->json('quote');
            $table->unsignedInteger('rank')->default(0);
            $table->enum('status', ['Published', 'Archived'])->default('Published');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
