<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_unit_types', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now = now();
        $defaults = [
            ['en' => 'Studio', 'ar' => 'ستوديو'],
            ['en' => '1+0', 'ar' => '1+0'],
            ['en' => '1+1', 'ar' => '1+1'],
            ['en' => '2+1', 'ar' => '2+1'],
            ['en' => '3+1', 'ar' => '3+1'],
            ['en' => '4+1', 'ar' => '4+1'],
            ['en' => 'Duplex', 'ar' => 'دوبلكس'],
            ['en' => 'Penthouse', 'ar' => 'بنتهاوس'],
            ['en' => 'Villa', 'ar' => 'فيلا'],
        ];

        $rows = [];
        $order = 10;
        foreach ($defaults as $translations) {
            $rows[] = [
                'name' => json_encode($translations, JSON_THROW_ON_ERROR),
                'sort_order' => $order,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $order += 10;
        }

        DB::table('project_unit_types')->insert($rows);
    }

    public function down(): void
    {
        Schema::dropIfExists('project_unit_types');
    }
};
