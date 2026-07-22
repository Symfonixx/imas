<?php

declare(strict_types=1);

namespace Modules\Base\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Base\Models\Media;
use Modules\Base\Models\MediaFolder;
use Modules\Base\Support\Media\MediaAssetResolver;

class ImportExistingMediaCommand extends Command
{
    protected $signature = 'media:import-existing {--dry-run : Report only without writing media rows}';

    protected $description = 'Register existing content image paths in the Media Library without changing content URLs.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $disk = Storage::disk('public');
        $stats = [
            'scanned' => 0,
            'imported' => 0,
            'existing' => 0,
            'missing' => 0,
            'external' => 0,
        ];

        $folder = $this->ensureImportFolder($dryRun);

        foreach ($this->discoverPaths() as $path) {
            $stats['scanned']++;
            $normalized = MediaAssetResolver::normalizePath($path);

            if ($normalized === null) {
                if (is_string($path) && (str_starts_with($path, 'http://') || str_starts_with($path, 'https://'))) {
                    $stats['external']++;
                }

                continue;
            }

            if (Media::query()->where('path', $normalized)->exists()) {
                $stats['existing']++;

                continue;
            }

            if (! $disk->exists($normalized)) {
                $stats['missing']++;
                $this->warn("Missing file: {$normalized}");

                continue;
            }

            if ($dryRun) {
                $stats['imported']++;
                $this->line("[dry-run] would import {$normalized}");

                continue;
            }

            $absolute = $disk->path($normalized);
            $size = @filesize($absolute) ?: 0;
            $mime = @mime_content_type($absolute) ?: null;
            $dimensions = @getimagesize($absolute);
            Media::query()->create([
                'folder_id' => $folder?->id,
                'name' => pathinfo($normalized, PATHINFO_FILENAME) ?: 'Imported media',
                'path' => $normalized,
                'disk' => 'public',
                'mime_type' => is_string($mime) ? $mime : null,
                'size' => (int) $size,
                'width' => is_array($dimensions) ? ((int) ($dimensions[0] ?? 0) ?: null) : null,
                'height' => is_array($dimensions) ? ((int) ($dimensions[1] ?? 0) ?: null) : null,
            ]);
            $stats['imported']++;
        }

        $this->table(array_keys($stats), [array_values($stats)]);

        return self::SUCCESS;
    }

    private function ensureImportFolder(bool $dryRun): ?MediaFolder
    {
        if ($dryRun) {
            return MediaFolder::query()->where('slug', 'imported-content')->first();
        }

        return MediaFolder::query()->firstOrCreate(
            ['slug' => 'imported-content'],
            [
                'name' => 'Imported Content',
                'user_id' => null,
            ]
        );
    }

    /**
     * @return list<string>
     */
    private function discoverPaths(): array
    {
        $paths = [];

        $scalarSources = [
            ['blogs', ['image', 'meta_image']],
            ['pages', ['image', 'meta_image']],
            ['slides', ['image']],
            ['blog_categories', ['meta_image']],
            ['corporate_services', ['image', 'meta_image']],
            ['teams', ['avatar']],
            ['testimonials', ['avatar']],
            ['properties', ['thumbnail']],
            ['property_slides', ['image']],
            ['property_slide_media', ['path']],
        ];

        foreach ($scalarSources as [$table, $columns]) {
            if (! $this->tableExists($table)) {
                continue;
            }
            foreach ($columns as $column) {
                if (! $this->columnExists($table, $column)) {
                    continue;
                }
                $rows = DB::table($table)->whereNotNull($column)->pluck($column);
                foreach ($rows as $value) {
                    if (is_string($value) && $value !== '') {
                        $paths[] = $value;
                    }
                }
            }
        }

        if ($this->tableExists('settings') && $this->columnExists('settings', 'key') && $this->columnExists('settings', 'value')) {
            $imageKeys = [
                'white_logo', 'black_logo', 'admin_logo', 'meta_img',
                'contact_us_banner', 'blog_show_banner', 'property_show_banner',
                'about_us_banner', 'turkish_citizenship_banner',
            ];
            $rows = DB::table('settings')->whereIn('key', $imageKeys)->pluck('value');
            foreach ($rows as $value) {
                if (is_string($value) && $value !== '') {
                    $paths[] = $value;
                }
            }
        }

        if ($this->tableExists('slide_categories') && $this->columnExists('slide_categories', 'images')) {
            $rows = DB::table('slide_categories')->whereNotNull('images')->pluck('images');
            foreach ($rows as $json) {
                $decoded = is_string($json) ? json_decode($json, true) : $json;
                if (! is_array($decoded)) {
                    continue;
                }
                foreach ($decoded as $item) {
                    if (is_string($item) && $item !== '') {
                        $paths[] = $item;
                    }
                }
            }
        }

        foreach ($this->discoverRichTextPaths() as $path) {
            $paths[] = $path;
        }

        return array_values(array_unique($paths));
    }

    /**
     * @return list<string>
     */
    private function discoverRichTextPaths(): array
    {
        $paths = [];
        $richTextSources = [
            ['blogs', 'content'],
            ['pages', 'content'],
            ['corporate_services', 'content'],
            ['properties', 'overview'],
            ['properties', 'why_to_buy'],
            ['properties', 'facilities'],
            ['properties', 'content'],
            ['settings', 'value'],
        ];

        foreach ($richTextSources as [$table, $column]) {
            if (! $this->tableExists($table) || ! $this->columnExists($table, $column)) {
                continue;
            }

            $rows = DB::table($table)->whereNotNull($column)->pluck($column);
            foreach ($rows as $value) {
                $html = is_string($value) ? $value : '';
                if ($html === '' && is_array(json_decode((string) $value, true))) {
                    $decoded = json_decode((string) $value, true);
                    $html = implode("\n", array_map('strval', $decoded));
                }
                if (! is_string($html) || $html === '') {
                    continue;
                }
                if (preg_match_all('/(?:src|data-media-path)=["\']([^"\']+)["\']/i', $html, $matches)) {
                    foreach ($matches[1] as $candidate) {
                        if (str_contains($candidate, 'storage/') || ! str_starts_with($candidate, 'http')) {
                            $paths[] = $candidate;
                        } elseif (str_contains($candidate, '/storage/')) {
                            $paths[] = $candidate;
                        }
                    }
                }
            }
        }

        return $paths;
    }

    private function tableExists(string $table): bool
    {
        try {
            return \Illuminate\Support\Facades\Schema::hasTable($table);
        } catch (\Throwable) {
            return false;
        }
    }

    private function columnExists(string $table, string $column): bool
    {
        try {
            return \Illuminate\Support\Facades\Schema::hasColumn($table, $column);
        } catch (\Throwable) {
            return false;
        }
    }
}
