<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_folders', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('media', function (Blueprint $table) {
            $table->foreignId('folder_id')
                ->nullable()
                ->after('id')
                ->constrained('media_folders')
                ->nullOnDelete();
            $table->string('alt_text')->nullable()->after('name');
            $table->string('title')->nullable()->after('alt_text');
            $table->text('caption')->nullable()->after('title');
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropConstrainedForeignId('folder_id');
            $table->dropColumn(['alt_text', 'title', 'caption']);
        });

        Schema::dropIfExists('media_folders');
    }
};
