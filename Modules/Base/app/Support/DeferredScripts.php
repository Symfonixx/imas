<?php

namespace Modules\Base\Support;

/**
 * Splits admin-injected HTML (GTM, analytics, pixels) so &lt;noscript&gt; can
 * render immediately while &lt;script&gt; tags wait until after first paint.
 */
final class DeferredScripts
{
    /**
     * @return array{immediate: string, deferred: string}
     */
    public static function split(?string $html): array
    {
        $html = trim((string) $html);
        if ($html === '') {
            return ['immediate' => '', 'deferred' => ''];
        }

        $immediate = '';
        $deferred = preg_replace_callback(
            '/<noscript\b[^>]*>.*?<\/noscript>/is',
            static function (array $match) use (&$immediate): string {
                $immediate .= $match[0];

                return '';
            },
            $html
        );

        return [
            'immediate' => trim($immediate),
            'deferred' => trim((string) $deferred),
        ];
    }
}
