<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_categories', function (Blueprint $table) {
            if (! Schema::hasColumn('blog_categories', 'meta_title')) {
                $table->json('meta_title')->nullable()->after('add_to_navbar');
            }
            if (! Schema::hasColumn('blog_categories', 'meta_description')) {
                $table->json('meta_description')->nullable()->after('meta_title');
            }
            if (! Schema::hasColumn('blog_categories', 'meta_keywords')) {
                $table->json('meta_keywords')->nullable()->after('meta_description');
            }
            if (! Schema::hasColumn('blog_categories', 'meta_image')) {
                $table->string('meta_image')->nullable()->after('meta_keywords');
            }
        });
    }

    public function down(): void
    {
        Schema::table('blog_categories', function (Blueprint $table) {
            $columns = array_filter(
                ['meta_title', 'meta_description', 'meta_keywords', 'meta_image'],
                fn (string $column) => Schema::hasColumn('blog_categories', $column)
            );

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
