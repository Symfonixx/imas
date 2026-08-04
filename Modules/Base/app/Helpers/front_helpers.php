<?php

if (! function_exists('front_trans')) {
    /**
     * Translate using flattened front-office module JSON keys (e.g. navBar.Home).
     */
    function front_trans(string $key, ?array $translations = null): string
    {
        $bag = $translations ?? (app('view')->shared('translations') ?? []);

        if (is_array($bag) && array_key_exists($key, $bag) && is_string($bag[$key])) {
            return $bag[$key];
        }

        return $key;
    }
}

if (! function_exists('format_turkish_phone')) {
    function format_turkish_phone(?string $phone): string
    {
        $raw = trim((string) $phone);
        if ($raw === '') {
            return '';
        }

        $digits = preg_replace('/\D+/', '', $raw) ?? '';
        if ($digits === '') {
            return $raw;
        }

        $national = $digits;
        if (str_starts_with($national, '90')) {
            $national = substr($national, 2);
        }
        if (str_starts_with($national, '0')) {
            $national = substr($national, 1);
        }
        if (strlen($national) !== 10) {
            return $raw;
        }

        return '+90 '.substr($national, 0, 3).' '.substr($national, 3, 3).' '.substr($national, 6, 2).' '.substr($national, 8, 2);
    }
}

if (! function_exists('normalize_turkish_phone_digits')) {
    function normalize_turkish_phone_digits(?string $phone): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone) ?? '';
        if ($digits === '') {
            return '';
        }

        $national = $digits;
        if (str_starts_with($national, '90')) {
            $national = substr($national, 2);
        }
        if (str_starts_with($national, '0')) {
            $national = substr($national, 1);
        }
        if (strlen($national) !== 10) {
            return '';
        }

        return '90'.$national;
    }
}

if (! function_exists('whatsapp_contact_href')) {
    function whatsapp_contact_href(?string $whatsapp, ?string $phone): string
    {
        $wa = trim((string) $whatsapp);
        if ($wa !== '') {
            if (preg_match('#^https?://#i', $wa)) {
                return $wa;
            }
            $digits = preg_replace('/\D+/', '', $wa) ?? '';
            if ($digits !== '') {
                return 'https://wa.me/'.$digits;
            }
        }

        $normalized = normalize_turkish_phone_digits($phone);
        if ($normalized !== '') {
            return 'https://wa.me/'.$normalized;
        }

        $fallback = preg_replace('/\D+/', '', (string) $phone) ?? '';

        return $fallback !== '' ? 'https://wa.me/'.$fallback : '#';
    }
}

if (! function_exists('gmail_compose_url')) {
    function gmail_compose_url(?string $email, ?string $subject = null, ?string $body = null): string
    {
        $to = trim((string) $email);
        if ($to === '') {
            return '';
        }

        $params = ['view' => 'cm', 'fs' => '1', 'to' => $to];
        if ($subject !== null && trim($subject) !== '') {
            $params['su'] = trim($subject);
        }
        if ($body !== null && trim($body) !== '') {
            $params['body'] = trim($body);
        }

        return 'https://mail.google.com/mail/?'.http_build_query($params);
    }
}

if (! function_exists('front_localized')) {
    /**
     * Resolve a Spatie-style localized string or plain string for the current locale.
     */
    function front_localized(mixed $value, ?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();

        if (is_string($value)) {
            return trim($value);
        }

        if (is_array($value)) {
            $pick = $value[$locale] ?? $value['en'] ?? reset($value);

            return is_string($pick) ? trim($pick) : '';
        }

        return '';
    }
}

if (! function_exists('front_strip_html')) {
    function front_strip_html(mixed $value): string
    {
        if (! is_string($value)) {
            return '';
        }

        return trim(preg_replace('/\s+/u', ' ', strip_tags($value)) ?? '');
    }
}

if (! function_exists('property_location_line')) {
    /**
     * @param  array{city?: array{name?: mixed}, district?: array{name?: mixed}, area?: array{name?: mixed}}|null  $location
     */
    function property_location_line(?array $location, ?string $locale = null): string
    {
        if ($location === null) {
            return '';
        }

        $parts = array_values(array_filter([
            front_localized($location['city']['name'] ?? null, $locale),
            front_localized($location['district']['name'] ?? null, $locale),
            front_localized($location['area']['name'] ?? null, $locale),
        ]));

        return implode(', ', $parts);
    }
}

if (! function_exists('format_property_money')) {
    function format_property_money(mixed $amount, ?string $locale = null): string
    {
        if (! is_numeric($amount)) {
            return '—';
        }

        $locale = $locale ?: app()->getLocale();
        $formatted = number_format((float) $amount, 0, '.', ',');

        // Keep a plain $ prefix (matches Vue formatPropertyMoney).
        return '$'.$formatted;
    }
}

if (! function_exists('property_start_price')) {
    /**
     * @param  array<string, mixed>  $property
     */
    function property_start_price(array $property): ?float
    {
        if (isset($property['start_price']) && is_numeric($property['start_price'])) {
            return (float) $property['start_price'];
        }

        if (isset($property['price']) && is_numeric($property['price'])) {
            return (float) $property['price'];
        }

        return null;
    }
}

if (! function_exists('youtube_hero_embed_src')) {
    /**
     * Extract a muted autoplay YouTube embed URL from iframe HTML or a watch/embed URL.
     */
    function youtube_hero_embed_src(?string $embed): string
    {
        $raw = trim((string) $embed);
        if ($raw === '') {
            return '';
        }

        $id = '';
        if (preg_match('#youtube\.com/embed/([a-zA-Z0-9_-]+)#', $raw, $m)) {
            $id = $m[1];
        } elseif (preg_match('#[?&]v=([a-zA-Z0-9_-]+)#', $raw, $m)) {
            $id = $m[1];
        } elseif (preg_match('#youtu\.be/([a-zA-Z0-9_-]+)#', $raw, $m)) {
            $id = $m[1];
        }

        if ($id === '') {
            return '';
        }

        return 'https://www.youtube.com/embed/'.$id.'?autoplay=1&mute=1&controls=0&loop=1&playlist='.$id.'&playsinline=1&rel=0';
    }
}
