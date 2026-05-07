<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attribute_family_attribute', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_family_id')
                ->constrained('attribute_families')
                ->cascadeOnDelete();
            $table->foreignId('attribute_id')
                ->constrained('attributes')
                ->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->unique(['attribute_family_id', 'attribute_id'], 'attr_fam_attr_unique');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attribute_family_attribute');
    }
};
