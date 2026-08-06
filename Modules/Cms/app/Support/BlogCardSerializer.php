<?php

namespace Modules\Cms\Support;

use Illuminate\Support\Str;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Modules\Base\Support\Media\MediaAssetResolver;
use Modules\Cms\Models\Blog;

final class BlogCardSerializer
{
    /**
     * Card / listing shape for storefront and public JSON APIs.
     *
     * @return array<string, mixed>
     */
    public static function toArray(Blog $blog): array
    {
        $description = (string) ($blog->description ?? '');
        $media = app(MediaAssetResolver::class)->resolve($blog->image);

        return [
            'id' => $blog->id,
            'title' => $blog->title,
            'slug' => $blog->slug,
            'description' => $blog->description,
            'excerpt' => Str::limit(strip_tags($description), 150),
            'image' => $media['url'] ?? $blog->image_link,
            'image_alt' => $media['alt_text'] ?: (string) $blog->title,
            'image_title' => $media['title'] ?? null,
            'featured' => (bool) $blog->featured,
            'visits' => (int) $blog->visits,
            'created_at' => $blog->created_at?->toIso8601String(),
            'updated_at' => $blog->updated_at?->toIso8601String(),
            'date' => $blog->created_at?->locale(app()->getLocale())->translatedFormat('d M Y') ?? '',
            'url' => LaravelLocalization::localizeUrl('/blog/'.$blog->slug),
            'category' => $blog->category
                ? [
                    'id' => $blog->category->id,
                    'name' => $blog->category->name,
                    'slug' => $blog->category->slug,
                ]
                : null,
        ];
    }

    /**
     * Full blog payload including content and SEO meta.
     *
     * @return array<string, mixed>
     */
    public static function toDetailArray(Blog $blog): array
    {
        $description = (string) ($blog->description ?? '');
        $metaTitle = $blog->meta_title;
        if ($metaTitle === null || trim((string) $metaTitle) === '') {
            $metaTitle = $blog->title;
        }
        $metaDescription = $blog->meta_description;
        if ($metaDescription === null || trim(strip_tags((string) $metaDescription)) === '') {
            $metaDescription = Str::limit(strip_tags($description), 160);
        }

        $resolver = app(MediaAssetResolver::class);
        $imageMedia = $resolver->resolve($blog->image);
        $metaMedia = $resolver->resolve($blog->meta_image);
        $card = self::toArray($blog);

        return array_merge($card, [
            'content' => $blog->content,
            'meta_image' => $metaMedia['url'] ?? $blog->meta_image_link,
            'meta' => [
                'title' => $metaTitle,
                'description' => $metaDescription,
                'keywords' => $blog->meta_keywords,
                'image' => $metaMedia['url'] ?? $blog->meta_image_link,
                'canonical_url' => LaravelLocalization::localizeUrl('/blog/'.$blog->slug),
            ],
            'image' => $imageMedia['url'] ?? $blog->image_link,
            'image_alt' => $imageMedia['alt_text'] ?: (string) $blog->title,
            'image_title' => $imageMedia['title'] ?? null,
        ]);
    }
}
