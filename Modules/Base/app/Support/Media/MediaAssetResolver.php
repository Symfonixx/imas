<?php

declare(strict_types=1);

namespace Modules\Base\Support\Media;

use Illuminate\Support\Collection;
use Modules\Base\Models\Media;

final class MediaAssetResolver
{
    /**
     * @param  list<string|null>  $paths
     * @return array<string, array{path: string, url: string, alt_text: ?string, title: ?string, caption: ?string, mime_type: ?string, width: ?int, height: ?int, archived: bool}>
     */
    public function resolveMany(array $paths): array
    {
        $normalized = collect($paths)
            ->map(fn ($path) => self::normalizePath(is_string($path) ? $path : null))
            ->filter()
            ->unique()
            ->values();

        if ($normalized->isEmpty()) {
            return [];
        }

        /** @var Collection<int, Media> $media */
        $media = Media::query()
            ->where('disk', 'public')
            ->whereIn('path', $normalized->all())
            ->get()
            ->keyBy('path');

        $resolved = [];
        foreach ($normalized as $path) {
            $item = $media->get($path);
            if ($item === null) {
                $resolved[$path] = [
                    'path' => $path,
                    'url' => asset('storage/'.$path),
                    'alt_text' => null,
                    'title' => null,
                    'caption' => null,
                    'mime_type' => null,
                    'width' => null,
                    'height' => null,
                    'archived' => false,
                ];

                continue;
            }

            $resolved[$path] = [
                'path' => $item->path,
                'url' => $item->url,
                'alt_text' => $item->alt_text,
                'title' => $item->title,
                'caption' => $item->caption,
                'mime_type' => $item->mime_type,
                'width' => $item->width,
                'height' => $item->height,
                'archived' => $item->archived_at !== null,
            ];
        }

        return $resolved;
    }

    /**
     * @return array{path: string, url: string, alt_text: ?string, title: ?string, caption: ?string, mime_type: ?string, width: ?int, height: ?int, archived: bool}|null
     */
    public function resolve(?string $path): ?array
    {
        $normalized = self::normalizePath($path);
        if ($normalized === null) {
            return null;
        }

        return $this->resolveMany([$normalized])[$normalized] ?? null;
    }

    public static function normalizePath(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);
        if ($value === '' || strcasecmp($value, 'null') === 0) {
            return null;
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            $path = parse_url($value, PHP_URL_PATH);
            if (! is_string($path) || $path === '') {
                return null;
            }
            $value = $path;
        }

        $value = ltrim($value, '/');
        if (str_starts_with($value, 'storage/')) {
            $value = substr($value, strlen('storage/'));
        }

        $value = ltrim($value, '/');

        return $value !== '' ? $value : null;
    }
}
