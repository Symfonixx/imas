<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Base\Models\Media;
use Modules\Base\Support\Media\MediaAssetResolver;
use Modules\Base\Support\Media\MediaPathRule;
use Tests\TestCase;

class MediaAssetResolverTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_resolves_paths_and_storage_urls_with_metadata(): void
    {
        Media::query()->create([
            'name' => 'Villa',
            'alt_text' => 'Sea view villa',
            'title' => 'Villa title',
            'caption' => 'Caption text',
            'path' => 'media-library/villa.jpg',
            'disk' => 'public',
            'mime_type' => 'image/jpeg',
            'size' => 1234,
            'width' => 1200,
            'height' => 800,
        ]);

        $resolver = new MediaAssetResolver;
        $fromPath = $resolver->resolve('media-library/villa.jpg');
        $fromUrl = $resolver->resolve('/storage/media-library/villa.jpg');

        $this->assertSame('Sea view villa', $fromPath['alt_text']);
        $this->assertSame('Villa title', $fromUrl['title']);
        $this->assertSame('Caption text', $fromUrl['caption']);
    }

    public function test_media_path_rule_rejects_unregistered_paths(): void
    {
        $failed = false;
        (new MediaPathRule)->validate('image', 'media-library/missing.jpg', function () use (&$failed) {
            $failed = true;
        });

        $this->assertTrue($failed);
    }

    public function test_media_path_rule_accepts_active_library_images(): void
    {
        Media::query()->create([
            'name' => 'Ok',
            'path' => 'media-library/ok.jpg',
            'disk' => 'public',
            'mime_type' => 'image/jpeg',
            'size' => 10,
        ]);

        $failed = false;
        (new MediaPathRule)->validate('image', 'media-library/ok.jpg', function () use (&$failed) {
            $failed = true;
        });

        $this->assertFalse($failed);
    }
}
