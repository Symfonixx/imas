<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_views', function (Blueprint $table) {
            $table->id();
            $table->string('path', 512);
            $table->string('route_name')->nullable()->index();
            $table->string('referrer_host')->nullable()->index();
            $table->char('visitor_hash', 64)->index();
            $table->date('viewed_on')->index();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['viewed_on', 'visitor_hash']);
            $table->index(['path', 'viewed_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_views');
    }
};
