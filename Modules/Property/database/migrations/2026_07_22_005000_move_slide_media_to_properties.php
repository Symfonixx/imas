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

        if (! Schema::hasTable('property_slide_media')) {
            Schema::create('property_slide_media', function (Blueprint $table) {
                $table->id();
                $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
                $table->foreignId('slide_category_id')->constrained('slide_categories')->cascadeOnDelete();
                $table->enum('type', ['image', 'video']);
                $table->string('path');
                $table->unsignedSmallInteger('position')->default(0);
                $table->timestamps();
                $table->index(['property_id', 'slide_category_id', 'type']);
            });
        }

        $this->moveCategoryMediaToLinkedProperties();
        $this->moveLegacyPropertySlides();

        Schema::table('slide_categories', function (Blueprint $table) {
            if (Schema::hasColumn('slide_categories', 'images')) {
                $table->dropColumn('images');
            }
            if (Schema::hasColumn('slide_categories', 'videos')) {
                $table->dropColumn('videos');
            }
            if (Schema::hasColumn('slide_categories', 'video')) {
                $table->dropColumn('video');
            }
        });

        Schema::dropIfExists('property_slides');
    }

    public function down(): void
    {
        if (! Schema::hasTable('slide_categories')) {
            return;
        }

        Schema::table('slide_categories', function (Blueprint $table) {
            if (! Schema::hasColumn('slide_categories', 'images')) {
                $table->json('images')->nullable()->after('slug');
            }
            if (! Schema::hasColumn('slide_categories', 'videos')) {
                $table->json('videos')->nullable()->after('images');
            }
        });

        if (Schema::hasTable('property_slide_media')) {
            $rows = DB::table('property_slide_media')
                ->select(['slide_category_id', 'type', 'path'])
                ->orderBy('position')
                ->get()
                ->groupBy('slide_category_id');

            foreach ($rows as $categoryId => $media) {
                DB::table('slide_categories')->where('id', $categoryId)->update([
                    'images' => json_encode($media->where('type', 'image')->pluck('path')->unique()->values()),
                    'videos' => json_encode($media->where('type', 'video')->pluck('path')->unique()->values()),
                ]);
            }

            if (! Schema::hasTable('property_slides')) {
                Schema::create('property_slides', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
                    $table->string('image');
                    $table->unsignedSmallInteger('position')->default(0);
                    $table->timestamps();
                });
            }

            foreach (DB::table('property_slide_media')->where('type', 'image')->get() as $image) {
                DB::table('property_slides')->insert([
                    'property_id' => $image->property_id,
                    'image' => $image->path,
                    'position' => $image->position,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        Schema::dropIfExists('property_slide_media');
    }

    private function moveCategoryMediaToLinkedProperties(): void
    {
        $hasImages = Schema::hasColumn('slide_categories', 'images');
        $hasVideos = Schema::hasColumn('slide_categories', 'videos');
        $hasVideo = Schema::hasColumn('slide_categories', 'video');

        if (! $hasImages && ! $hasVideos && ! $hasVideo) {
            return;
        }

        $columns = ['id'];
        foreach (['images', 'videos', 'video'] as $column) {
            if (Schema::hasColumn('slide_categories', $column)) {
                $columns[] = $column;
            }
        }

        foreach (DB::table('slide_categories')->select($columns)->get() as $category) {
            $propertyIds = DB::table('property_slide_category')
                ->where('slide_category_id', $category->id)
                ->pluck('property_id');
            $images = $hasImages ? $this->decodePaths($category->images ?? null) : [];
            $videos = $hasVideos ? $this->decodePaths($category->videos ?? null) : [];
            if ($hasVideo && filled($category->video ?? null)) {
                $videos[] = (string) $category->video;
            }

            if ($propertyIds->isEmpty() && ($images !== [] || $videos !== [])) {
                throw new RuntimeException(
                    "Slide category {$category->id} has media but is not linked to a property. "
                    .'Link or remove that media before running this migration.'
                );
            }

            foreach ($propertyIds as $propertyId) {
                $this->insertMedia((int) $propertyId, (int) $category->id, 'image', $images);
                $this->insertMedia((int) $propertyId, (int) $category->id, 'video', $videos);
            }
        }
    }

    private function moveLegacyPropertySlides(): void
    {
        if (! Schema::hasTable('property_slides')) {
            return;
        }

        $slides = DB::table('property_slides')->orderBy('position')->get();
        if ($slides->isEmpty()) {
            return;
        }

        $categoryId = DB::table('slide_categories')->where('slug', 'legacy-slides')->value('id');
        if ($categoryId === null) {
            $categoryId = DB::table('slide_categories')->insertGetId([
                'name' => json_encode(['en' => 'Legacy slides']),
                'description' => 'Automatically migrated property slides.',
                'slug' => 'legacy-slides',
                'status' => 'Published',
                'position' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ($slides->groupBy('property_id') as $propertyId => $propertySlides) {
            DB::table('property_slide_category')->insertOrIgnore([
                'property_id' => $propertyId,
                'slide_category_id' => $categoryId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($propertySlides as $slide) {
                $alreadyMigrated = DB::table('property_slide_media')
                    ->where('property_id', $propertyId)
                    ->where('slide_category_id', $categoryId)
                    ->where('type', 'image')
                    ->where('path', $slide->image)
                    ->exists();
                if ($alreadyMigrated) {
                    continue;
                }

                DB::table('property_slide_media')->insert([
                    'property_id' => $propertyId,
                    'slide_category_id' => $categoryId,
                    'type' => 'image',
                    'path' => $slide->image,
                    'position' => $slide->position,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * @return list<string>
     */
    private function decodePaths(mixed $value): array
    {
        if (! is_string($value) || $value === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded)
            ? array_values(array_filter(array_map('strval', $decoded)))
            : [];
    }

    /**
     * @param  list<string>  $paths
     */
    private function insertMedia(
        int $propertyId,
        int $categoryId,
        string $type,
        array $paths
    ): void {
        foreach (array_values(array_unique($paths)) as $position => $path) {
            $alreadyMigrated = DB::table('property_slide_media')
                ->where('property_id', $propertyId)
                ->where('slide_category_id', $categoryId)
                ->where('type', $type)
                ->where('path', $path)
                ->exists();
            if ($alreadyMigrated) {
                continue;
            }

            DB::table('property_slide_media')->insert([
                'property_id' => $propertyId,
                'slide_category_id' => $categoryId,
                'type' => $type,
                'path' => $path,
                'position' => $position,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
};
