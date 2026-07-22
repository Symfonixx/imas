<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('slide_categories')) {
            return;
        }

        if (Schema::hasColumn('slide_categories', 'video') && ! Schema::hasColumn('slide_categories', 'videos')) {
            Schema::table('slide_categories', function (Blueprint $table) {
                $table->json('videos')->nullable()->after('images');
            });

            $rows = DB::table('slide_categories')->select(['id', 'video'])->get();
            foreach ($rows as $row) {
                $videos = filled($row->video) ? [$row->video] : [];
                DB::table('slide_categories')
                    ->where('id', $row->id)
                    ->update(['videos' => json_encode($videos)]);
            }

            Schema::table('slide_categories', function (Blueprint $table) {
                $table->dropColumn('video');
            });

            return;
        }

        // Fresh installs no longer store media on categories.
    }

    public function down(): void
    {
        if (! Schema::hasTable('slide_categories') || ! Schema::hasColumn('slide_categories', 'videos')) {
            return;
        }

        if (! Schema::hasColumn('slide_categories', 'video')) {
            Schema::table('slide_categories', function (Blueprint $table) {
                $table->string('video')->nullable()->after('images');
            });
        }

        $rows = DB::table('slide_categories')->select(['id', 'videos'])->get();
        foreach ($rows as $row) {
            $videos = json_decode((string) $row->videos, true);
            $first = is_array($videos) ? collect($videos)->filter()->first() : null;
            DB::table('slide_categories')
                ->where('id', $row->id)
                ->update(['video' => $first]);
        }

        Schema::table('slide_categories', function (Blueprint $table) {
            $table->dropColumn('videos');
        });
    }
};
