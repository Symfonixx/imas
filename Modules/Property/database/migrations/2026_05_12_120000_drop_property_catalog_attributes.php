<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('property_attribute_values');
        Schema::dropIfExists('attribute_family_attribute');

        if (Schema::hasTable('property_types') && Schema::hasColumn('property_types', 'attribute_family_id')) {
            Schema::table('property_types', function (Blueprint $table) {
                $table->dropForeign(['attribute_family_id']);
                $table->dropColumn('attribute_family_id');
            });
        }

        Schema::dropIfExists('attributes');
        Schema::dropIfExists('attribute_families');
    }

    public function down(): void
    {
        // Intentionally empty: legacy attribute catalog is not restored.
    }
};
