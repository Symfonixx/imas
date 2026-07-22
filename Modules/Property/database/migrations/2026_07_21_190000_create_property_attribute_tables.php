<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_attributes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->json('name');
            $table->json('help_text')->nullable();
            $table->string('type');
            $table->boolean('is_required')->default(false);
            $table->boolean('is_unique')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('validation')->nullable();
            $table->text('regex')->nullable();
            $table->json('default_value')->nullable();
            $table->timestamps();
        });

        Schema::create('property_attribute_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_id')
                ->constrained('property_attributes')
                ->cascadeOnDelete();
            $table->json('label');
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['attribute_id', 'position']);
        });

        Schema::create('property_attribute_groups', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'position']);
        });

        Schema::create('property_attribute_group_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')
                ->constrained('property_attribute_groups')
                ->cascadeOnDelete();
            $table->foreignId('attribute_id')
                ->constrained('property_attributes')
                ->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);

            $table->unique('attribute_id');
            $table->unique(['group_id', 'attribute_id']);
            $table->index(['group_id', 'position']);
        });

        Schema::create('property_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')
                ->constrained('properties')
                ->cascadeOnDelete();
            $table->foreignId('attribute_id')
                ->constrained('property_attributes')
                ->cascadeOnDelete();
            $table->text('text_value')->nullable();
            $table->decimal('decimal_value', 20, 6)->nullable();
            $table->boolean('boolean_value')->nullable();
            $table->unsignedBigInteger('integer_value')->nullable();
            $table->date('date_value')->nullable();
            $table->dateTime('datetime_value')->nullable();
            $table->json('json_value')->nullable();
            $table->string('unique_hash', 64)->nullable();
            $table->timestamps();

            $table->unique(['property_id', 'attribute_id']);
            $table->unique(['attribute_id', 'unique_hash']);
            $table->index(['attribute_id', 'decimal_value']);
            $table->index(['attribute_id', 'boolean_value']);
            $table->index(['attribute_id', 'integer_value']);
            $table->index(['attribute_id', 'date_value']);
            $table->index(['attribute_id', 'datetime_value']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_attribute_values');
        Schema::dropIfExists('property_attribute_group_mappings');
        Schema::dropIfExists('property_attribute_groups');
        Schema::dropIfExists('property_attribute_options');
        Schema::dropIfExists('property_attributes');
    }
};
