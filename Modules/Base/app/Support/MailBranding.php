<?php

namespace Modules\Base\Support;

use Modules\Base\Models\Seo;
use Modules\Base\Models\Settings;

/**
 * Resolves company branding (name, logo, contact, socials) for outbound email
 * from admin settings, mirroring the front-office logic in HandleInertiaRequests.
 * Used by the branded markdown mail views under resources/views/vendor/mail.
 */
class MailBranding
{
    /**
     * Company / website display name (SEO "website_name", else app name).
     */
    public static function name(): string
    {
        $name = trim((string) (Seo::get('website_name') ?: ''));

        return $name !== '' ? $name : (string) config('app.name');
    }

    /**
     * Public site URL used for the email header link.
     */
    public static function url(): string
    {
        $url = trim((string) config('app.url'));

        return $url !== '' ? rtrim($url, '/') : '/';
    }

    /**
     * Absolute URL to the company logo, or null when none is configured.
     * Prefers the white (dark-background) logo to match the email's dark theme.
     */
    public static function logoUrl(): ?string
    {
        foreach (['white_logo', 'admin_logo', 'black_logo'] as $key) {
            $path = trim((string) (Settings::get($key) ?: ''));

            if ($path === '' || $path === 'default.jpg') {
                continue;
            }

            if (preg_match('#^https?://#i', $path)) {
                return $path;
            }

            return asset('storage/'.ltrim($path, '/'));
        }

        return null;
    }

    /**
     * Short tagline shown under the footer name (SEO "website_desc").
     */
    public static function tagline(): string
    {
        return trim((string) (Seo::get('website_desc') ?: ''));
    }

    public static function contactEmail(): string
    {
        return trim((string) (Settings::get('email') ?: config('mail.from.address') ?: ''));
    }

    public static function contactPhone(): string
    {
        return trim((string) (Settings::get('phone') ?: ''));
    }

    public static function address(): string
    {
        return trim((string) (Settings::get('address') ?: ''));
    }

    /**
     * Non-empty social profile URLs keyed by network.
     *
     * @return array<string, string>
     */
    public static function social(): array
    {
        $networks = ['facebook', 'instagram', 'twitter', 'youtube', 'tiktok', 'whatsapp'];

        $out = [];
        foreach ($networks as $network) {
            $url = trim((string) (Settings::get($network) ?: ''));
            if ($url !== '') {
                $out[$network] = $url;
            }
        }

        return $out;
    }
}
