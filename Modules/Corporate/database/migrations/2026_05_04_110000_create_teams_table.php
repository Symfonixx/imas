<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->string('avatar')->nullable();
            $table->json('position')->nullable();
            $table->string('link')->nullable();
            $table->unsignedInteger('rank')->default(0);
            $table->enum('status', ['Published', 'Archived'])->default('Published');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
