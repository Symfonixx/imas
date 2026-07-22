<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('properties', 'url_key')) {
            Schema::table('properties', function (Blueprint $table) {
                $table->string('url_key', 191)->nullable()->after('project_code');
            });

            DB::table('properties')
                ->select(['id', 'project_code', 'url_key'])
                ->orderBy('id')
                ->chunkById(100, function ($rows): void {
                    foreach ($rows as $row) {
                        if (is_string($row->url_key) && $row->url_key !== '') {
                            continue;
                        }

                        $base = Str::slug((string) $row->project_code);
                        if ($base === '') {
                            $base = 'property-'.$row->id;
                        }

                        $candidate = $base;
                        $suffix = 1;
                        while (
                            DB::table('properties')
                                ->where('url_key', $candidate)
                                ->where('id', '!=', $row->id)
                                ->exists()
                        ) {
                            $candidate = $base.'-'.$suffix;
                            $suffix++;
                        }

                        DB::table('properties')
                            ->where('id', $row->id)
                            ->update(['url_key' => $candidate]);
                    }
                });

            Schema::table('properties', function (Blueprint $table) {
                $table->unique('url_key');
            });
        }

        if (Schema::hasTable('property_attribute_groups')
            && ! Schema::hasColumn('properties', 'attribute_group_id')
        ) {
            Schema::table('properties', function (Blueprint $table) {
                $table->foreignId('attribute_group_id')
                    ->nullable()
                    ->after('property_type_id')
                    ->constrained('property_attribute_groups')
                    ->nullOnDelete();
            });
        }

        if (Schema::hasColumn('properties', 'facilities')) {
            Schema::table('properties', function (Blueprint $table) {
                $table->dropColumn('facilities');
            });
        }

        if (DB::getDriverName() !== 'sqlite') {
            $this->relaxMysqlColumns();
        }

    }

    private function relaxMysqlColumns(): void
    {
        try {
            Schema::table('properties', function (Blueprint $table) {
                $table->dropForeign(['location_id']);
            });
        } catch (Throwable) {
            // Foreign key may already be absent on some environments.
        }

        Schema::table('properties', function (Blueprint $table) {
            $table->unsignedBigInteger('location_id')->nullable()->change();
            $table->json('overview')->nullable()->change();
            $table->json('why_to_buy')->nullable()->change();
            $table->json('content')->nullable()->change();
        });

        Schema::table('properties', function (Blueprint $table) {
            $table->foreign('location_id')
                ->references('id')
                ->on('locations')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::create('property_slides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')
                ->constrained('properties')
                ->cascadeOnDelete();
            $table->string('image');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::table('properties', function (Blueprint $table) {
            if (! Schema::hasColumn('properties', 'facilities')) {
                $table->json('facilities')->nullable();
            }
            if (Schema::hasColumn('properties', 'attribute_group_id')) {
                $table->dropConstrainedForeignId('attribute_group_id');
            }
            if (Schema::hasColumn('properties', 'url_key')) {
                $table->dropUnique(['url_key']);
                $table->dropColumn('url_key');
            }
        });
    }
};
