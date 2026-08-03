@php
    /** @var array<string, mixed> $seo */
    $seo = $seo ?? [];
    $title = $seo['title'] ?? config('app.name');
    $description = $seo['description'] ?? null;
    $keywords = $seo['keywords'] ?? null;
    $image = $seo['image'] ?? null;
    $canonical = $seo['canonical'] ?? url()->current();
    $ogType = $seo['og_type'] ?? 'website';
    $twitterCard = $seo['twitter_card'] ?? 'summary_large_image';
    $robots = $seo['robots'] ?? null;
    $siteName = $seo['site_name'] ?? ($appName ?? config('app.name'));
    $ogLocale = $seo['og_locale'] ?? 'en_US';
    $ogLocaleAlternates = $seo['og_locale_alternates'] ?? [];
    $hreflang = $seo['hreflang'] ?? [];
    $jsonLdBlocks = $seo['json_ld'] ?? [];
@endphp
<title>{{ $title }}</title>
@if ($description)
    <meta name="description" content="{{ $description }}">
@endif
@if ($keywords)
    <meta name="keywords" content="{{ $keywords }}">
@endif
@if ($robots)
    <meta name="robots" content="{{ $robots }}">
@endif
<link rel="canonical" href="{{ $canonical }}">

<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:locale" content="{{ $ogLocale }}">
@foreach ($ogLocaleAlternates as $alt)
    <meta property="og:locale:alternate" content="{{ $alt }}">
@endforeach
<meta property="og:type" content="{{ $ogType }}">
<meta property="og:title" content="{{ $title }}">
@if ($description)
    <meta property="og:description" content="{{ $description }}">
@endif
@if ($image)
    <meta property="og:image" content="{{ $image }}">
@endif
<meta property="og:url" content="{{ $canonical }}">

<meta name="twitter:card" content="{{ $twitterCard }}">
<meta name="twitter:title" content="{{ $title }}">
@if ($description)
    <meta name="twitter:description" content="{{ $description }}">
@endif
@if ($image)
    <meta name="twitter:image" content="{{ $image }}">
@endif

@foreach ($hreflang as $alt)
    <link rel="alternate" hreflang="{{ $alt['hreflang'] }}" href="{{ $alt['url'] }}">
@endforeach

@foreach ($jsonLdBlocks as $block)
    @php
        $json = is_string($block)
            ? $block
            : json_encode($block, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
    @endphp
    @if ($json)
        <script type="application/ld+json">{!! $json !!}</script>
    @endif
@endforeach
