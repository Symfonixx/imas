<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! DB::table('settings')->where('key', 'property_show_banner')->exists()) {
            DB::table('settings')->insert([
                'key' => 'property_show_banner',
                'value' => 'default.jpg',
            ]);
        }
    }

    public function down(): void
    {
        DB::table('settings')->where('key', 'property_show_banner')->delete();
    }
};
