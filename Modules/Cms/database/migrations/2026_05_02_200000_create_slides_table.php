<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slides', function (Blueprint $table) {
            $table->id();
            $table->string('image');
            $table->json('main_title')->nullable();
            $table->json('subtitle')->nullable();
            $table->string('link')->nullable();
            $table->integer('rank')->default(0)->index();
            $table->enum('status', ['Published', 'Archived'])->default('Published');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slides');
    }
};
