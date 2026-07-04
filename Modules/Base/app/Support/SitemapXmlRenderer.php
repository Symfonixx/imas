<?php

namespace Modules\Base\Support;

class SitemapXmlRenderer
{
    /**
     * @param  list<array{
     *     loc: string,
     *     lastmod: string|null,
     *     changefreq: string,
     *     priority: string,
     *     alternates: array<string, string>,
     *     xDefault: string
     * }>  $entries
     */
    public function render(array $entries): string
    {
        $lines = [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"',
            '        xmlns:xhtml="http://www.w3.org/1999/xhtml">',
        ];

        foreach ($entries as $entry) {
            $lines[] = '  <url>';
            $lines[] = '    <loc>'.$this->escape($entry['loc']).'</loc>';

            if (! empty($entry['lastmod'])) {
                $lines[] = '    <lastmod>'.$this->escape($entry['lastmod']).'</lastmod>';
            }

            $lines[] = '    <changefreq>'.$this->escape($entry['changefreq']).'</changefreq>';
            $lines[] = '    <priority>'.$this->escape($entry['priority']).'</priority>';

            foreach ($entry['alternates'] as $hreflang => $href) {
                $lines[] = sprintf(
                    '    <xhtml:link rel="alternate" hreflang="%s" href="%s" />',
                    $this->escape($hreflang),
                    $this->escape($href),
                );
            }

            $lines[] = sprintf(
                '    <xhtml:link rel="alternate" hreflang="x-default" href="%s" />',
                $this->escape($entry['xDefault']),
            );
            $lines[] = '  </url>';
        }

        $lines[] = '</urlset>';

        return implode("\n", $lines)."\n";
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }
}
