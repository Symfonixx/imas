<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_attribute_group', function (Blueprint $table) {
            $table->foreignId('property_id')
                ->constrained('properties')
                ->cascadeOnDelete();
            $table->foreignId('attribute_group_id')
                ->constrained('property_attribute_groups')
                ->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->primary(['property_id', 'attribute_group_id']);
            $table->index(['attribute_group_id', 'position']);
        });

        if (Schema::hasColumn('properties', 'attribute_group_id')) {
            $rows = DB::table('properties')
                ->whereNotNull('attribute_group_id')
                ->get(['id', 'attribute_group_id']);

            $now = now();
            foreach ($rows as $row) {
                DB::table('property_attribute_group')->insertOrIgnore([
                    'property_id' => $row->id,
                    'attribute_group_id' => $row->attribute_group_id,
                    'position' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            Schema::table('properties', function (Blueprint $table) {
                $table->dropConstrainedForeignId('attribute_group_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->foreignId('attribute_group_id')
                ->nullable()
                ->after('property_type_id')
                ->constrained('property_attribute_groups')
                ->nullOnDelete();
        });

        $firstGroups = DB::table('property_attribute_group')
            ->orderBy('position')
            ->orderBy('attribute_group_id')
            ->get()
            ->groupBy('property_id');

        foreach ($firstGroups as $propertyId => $groups) {
            $first = $groups->first();
            if ($first === null) {
                continue;
            }

            DB::table('properties')
                ->where('id', $propertyId)
                ->update(['attribute_group_id' => $first->attribute_group_id]);
        }

        Schema::dropIfExists('property_attribute_group');
    }
};
