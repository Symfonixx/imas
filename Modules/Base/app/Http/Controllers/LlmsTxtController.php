<?php

namespace Modules\Base\Http\Controllers;

use Illuminate\Http\Response;
use Modules\Base\Application\Seo\SeoDocumentService;
use Modules\Base\Models\Seo;

/**
 * Serves /llms.txt — machine-readable site summary for AI agents (Lighthouse Agentic).
 */
class LlmsTxtController
{
    public function __invoke(SeoDocumentService $seo): Response
    {
        $siteName = $seo->siteName();
        $description = trim((string) (
            Seo::get('site_meta_description')
            ?: Seo::get('website_desc')
            ?: ''
        ));
        if ($description === '') {
            $description = "{$siteName} — real estate listings and Turkish citizenship property guidance.";
        }

        $origin = rtrim((string) config('app.url'), '/');
        $lines = [
            '# '.$siteName,
            '',
            '> '.$description,
            '',
            '## Site',
            '',
            "- Home: {$origin}/",
            "- Properties: {$origin}/property",
            "- Blog: {$origin}/blog",
            "- Turkish Citizenship: {$origin}/turkish-citizenship",
            "- About: {$origin}/about-us",
            "- Contact: {$origin}/contact-us",
            '',
            '## Feeds',
            '',
            "- Sitemap: {$origin}/sitemap.xml",
            "- RSS: {$origin}/feed.xml",
            "- Robots: {$origin}/robots.txt",
            '',
            '## Notes',
            '',
            '- Locales: en, ar, tr (URL prefix for non-default locales).',
            '- Primary audience: buyers and investors seeking property in Turkey.',
            '',
        ];

        return response(implode("\n", $lines), 200)
            ->header('Content-Type', 'text/plain; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=3600');
    }
}
