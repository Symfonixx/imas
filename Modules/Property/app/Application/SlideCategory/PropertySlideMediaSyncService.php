<?php

namespace Modules\Property\Application\SlideCategory;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Base\Support\Media\MediaAssetResolver;
use Modules\Property\Models\Property;
use Modules\Property\Models\PropertySlideMedia;
use Throwable;

class PropertySlideMediaSyncService
{
    /**
     * @param  list<int|string>  $slideCategoryIds
     */
    public function synchronize(
        Request $request,
        Property $property,
        array $slideCategoryIds
    ): PropertySlideMediaChanges {
        $disk = Storage::disk('public');
        $changes = new PropertySlideMediaChanges($disk);
        $categoryIds = array_values(array_unique(array_filter(
            array_map('intval', $slideCategoryIds),
            static fn (int $id): bool => $id > 0
        )));

        try {
            $removeIds = array_values(array_unique(array_map(
                'intval',
                (array) $request->input('remove_slide_media_ids', [])
            )));

            $mediaToRemove = $property->slideMedia()
                ->where(function ($query) use ($removeIds, $categoryIds): void {
                    if ($removeIds !== []) {
                        $query->whereIn('id', $removeIds);
                    }
                    if ($categoryIds === []) {
                        $query->orWhereNotNull('slide_category_id');
                    } else {
                        $query->orWhereNotIn('slide_category_id', $categoryIds);
                    }
                })
                ->get();

            foreach ($mediaToRemove as $media) {
                $changes->trackSuperseded($media->path);
                $media->delete();
            }

            foreach ($categoryIds as $categoryId) {
                $position = (int) $property->slideMedia()
                    ->where('slide_category_id', $categoryId)
                    ->max('position') + 1;

                $position = $this->storeLibraryImages(
                    (array) $request->input("slide_media.{$categoryId}.images_media_paths", []),
                    $property,
                    $categoryId,
                    $position,
                    $changes
                );

                $this->storeFiles(
                    $request->file("slide_media.{$categoryId}.videos", []),
                    $property,
                    $categoryId,
                    PropertySlideMedia::TYPE_VIDEO,
                    $position,
                    $changes
                );
            }
        } catch (Throwable $exception) {
            $changes->rollback();

            throw $exception;
        }

        return $changes;
    }

    /**
     * @param  list<string|null>  $paths
     */
    private function storeLibraryImages(
        array $paths,
        Property $property,
        int $categoryId,
        int $position,
        PropertySlideMediaChanges $changes
    ): int {
        foreach ($paths as $rawPath) {
            $path = MediaAssetResolver::normalizePath(is_string($rawPath) ? $rawPath : null);
            if ($path === null) {
                continue;
            }

            $exists = $property->slideMedia()
                ->where('slide_category_id', $categoryId)
                ->where('type', PropertySlideMedia::TYPE_IMAGE)
                ->where('path', $path)
                ->exists();
            if ($exists) {
                continue;
            }

            $property->slideMedia()->create([
                'slide_category_id' => $categoryId,
                'type' => PropertySlideMedia::TYPE_IMAGE,
                'path' => $path,
                'position' => $position++,
            ]);
        }

        return $position;
    }

    /**
     * @param  array<int, UploadedFile>|UploadedFile|null  $files
     */
    private function storeFiles(
        array|UploadedFile|null $files,
        Property $property,
        int $categoryId,
        string $type,
        int $position,
        PropertySlideMediaChanges $changes
    ): int {
        $files = $files instanceof UploadedFile ? [$files] : ($files ?? []);
        $folder = $type === PropertySlideMedia::TYPE_IMAGE ? 'images' : 'videos';

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $path = $file->store(
                "properties/slides/{$property->id}/{$categoryId}/{$folder}",
                'public'
            );
            $changes->trackNew($path);

            $property->slideMedia()->create([
                'slide_category_id' => $categoryId,
                'type' => $type,
                'path' => $path,
                'position' => $position++,
            ]);
        }

        return $position;
    }
}
