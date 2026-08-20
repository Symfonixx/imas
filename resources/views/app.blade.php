@php
    $fh = asset('theme/findhouses');
    $feedSiteName = trim((string) (\Modules\Base\Models\Seo::get('website_name') ?: config('app.name')));
    $settings = \Modules\Base\Models\Settings::pluck('value', 'key');
    $seo = $seo ?? app(\Modules\Base\Application\Seo\SeoDocumentService::class)->documentSeo();
    $seoTitle = $seo['title'] ?? $feedSiteName;
    $seoDescription = $seo['description'] ?? '';
    $seoKeywords = $seo['keywords'] ?? '';
    $seoOgImage = $seo['og_image'] ?? '';
    $seoCanonical = $seo['canonical'] ?? '';
    $seoOgType = $seo['og_type'] ?? 'website';
    $seoRobots = $seo['robots'] ?? '';
    $seoJsonLd = $seo['json_ld'] ?? [];
    $seoOgSiteName = $seo['og_site_name'] ?? $feedSiteName;
    $seoOgLocale = $seo['og_locale'] ?? 'en_US';
    $seoOgLocaleAlternates = is_array($seo['og_locale_alternates'] ?? null) ? $seo['og_locale_alternates'] : [];
    $seoHreflang = is_array($seo['hreflang'] ?? null) ? $seo['hreflang'] : [];
    $seoThemeColor = $seo['theme_color'] ?? \Modules\Base\Application\Seo\SeoDocumentService::THEME_COLOR;
    $seoArticlePublished = $seo['article_published_time'] ?? '';
    $seoArticleModified = $seo['article_modified_time'] ?? '';
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/favicon/favicon-96x96.png') }}" sizes="96x96"/>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/favicon/favicon.svg') }}"/>
    <link rel="shortcut icon" href="{{ asset('images/favicon/favicon.ico') }}"/>
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/favicon/apple-touch-icon.png') }}"/>
    <link rel="manifest" href="{{ asset('images/favicon/site.webmanifest') }}"/>
    <link rel="alternate" type="application/rss+xml" title="{{ $feedSiteName }} Blog" href="{{ url('/feed.xml') }}">

    {{-- Dispatch SSR once so Blade SEO can yield when @inertiaHead already has matching tags. --}}
    @php
        if (! isset($__inertiaSsrDispatched)) {
            $__inertiaSsrDispatched = true;
            $__inertiaSsrResponse = app(\Inertia\Ssr\Gateway::class)->dispatch($page ?? []);
        }
        $seoFromSsr = (bool) $__inertiaSsrResponse;
    @endphp

    {{-- Document SEO in View Page Source when SSR is off/unavailable. inertia="" lets Vue Head replace on SPA navigations. --}}
    @unless ($seoFromSsr)
        <title inertia>{{ $seoTitle }}</title>
        <meta inertia="description" name="description" content="{{ $seoDescription }}">
        <meta inertia="og:description" property="og:description" content="{{ $seoDescription }}">
        @if ($seoKeywords !== '')
            <meta inertia="keywords" name="keywords" content="{{ $seoKeywords }}">
        @endif
        <meta inertia="og:title" property="og:title" content="{{ $seoTitle }}">
        <meta inertia="og:type" property="og:type" content="{{ $seoOgType }}">
        @if ($seoOgSiteName !== '')
            <meta inertia="og:site_name" property="og:site_name" content="{{ $seoOgSiteName }}">
        @endif
        <meta inertia="og:locale" property="og:locale" content="{{ $seoOgLocale }}">
        @foreach ($seoOgLocaleAlternates as $seoOgLocaleAlt)
            <meta inertia="{{ $seoOgLocaleAlt['key'] }}" property="og:locale:alternate" content="{{ $seoOgLocaleAlt['value'] }}">
        @endforeach
        @if ($seoOgImage !== '')
            <meta inertia="og:image" property="og:image" content="{{ $seoOgImage }}">
        @endif
        @if ($seoCanonical !== '')
            <link inertia="canonical" rel="canonical" href="{{ $seoCanonical }}">
            <meta inertia="og:url" property="og:url" content="{{ $seoCanonical }}">
        @endif
        @foreach ($seoHreflang as $seoHreflangItem)
            <link inertia="{{ $seoHreflangItem['key'] }}" rel="alternate" hreflang="{{ $seoHreflangItem['hreflang'] }}" href="{{ $seoHreflangItem['href'] }}">
        @endforeach
        @if ($seoRobots !== '')
            <meta inertia="robots" name="robots" content="{{ $seoRobots }}">
        @endif
        @if ($seoArticlePublished !== '')
            <meta inertia="article:published_time" property="article:published_time" content="{{ $seoArticlePublished }}">
        @endif
        @if ($seoArticleModified !== '')
            <meta inertia="article:modified_time" property="article:modified_time" content="{{ $seoArticleModified }}">
        @endif
        <meta name="theme-color" content="{{ $seoThemeColor }}">
        <meta inertia="twitter:card" name="twitter:card" content="{{ $seoOgImage !== '' ? 'summary_large_image' : 'summary' }}">
        <meta inertia="twitter:title" name="twitter:title" content="{{ $seoTitle }}">
        <meta inertia="twitter:description" name="twitter:description" content="{{ $seoDescription }}">
        @if ($seoOgImage !== '')
            <meta inertia="twitter:image" name="twitter:image" content="{{ $seoOgImage }}">
        @endif

        {{-- Structured data. Values are encoded by SeoDocumentService with JSON_HEX_TAG|JSON_HEX_AMP. --}}
        @foreach ($seoJsonLd as $seoJsonLdKey => $seoJsonLdBlock)
            <script inertia="{{ $seoJsonLdKey }}" type="application/ld+json">{!! $seoJsonLdBlock !!}</script>
        @endforeach
    @else
        {{-- theme-color is not managed by Vue Head; keep it when SSR supplies the rest. --}}
        <meta name="theme-color" content="{{ $seoThemeColor }}">
    @endunless

    {{-- Brand font (public/fonts/Avenir_LT_Std_55_Roman.otf) — asset() so URL works in dev + subfolder deploys --}}
    <link rel="preload" href="{{ asset('fonts/Avenir_LT_Std_55_Roman.otf') }}" as="font" type="font/otf" crossorigin="anonymous">
    <style>
        @font-face {
            font-family: "Avenir LT Std 55 Roman";
            src: url("{{ asset('fonts/Avenir_LT_Std_55_Roman.otf') }}") format("opentype");
            font-weight: 400;
            font-style: normal;
            font-display: swap;
        }
    </style>

    {{--
      Critical Find Houses CSS only (render-blocking). Unused plugin skins
      (AOS, animate, magnific, lightcase, maps, pink, flaticon, jquery-ui)
      removed — jquery-ui CSS loads with the hero range slider; owl with testimonials.
      FA4 + FA5 both required: app.css stacks FontAwesome first for .fa icons.
    --}}
    <link rel="stylesheet" href="{{ $fh }}/css/font-awesome.min.css">
    <link rel="stylesheet" href="{{ $fh }}/css/fontawesome-5-all.min.css">
    <link rel="stylesheet" href="{{ $fh }}/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ $fh }}/css/menu.css">
    <link rel="stylesheet" href="{{ $fh }}/css/search.css">
    <link rel="stylesheet" href="{{ $fh }}/css/slick.css">
    <link rel="stylesheet" href="{{ $fh }}/css/styles.css">

    {{-- Below-fold / conditional theme CSS — non-blocking --}}
    <link rel="stylesheet" href="{{ $fh }}/css/owl.carousel.min.css" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="{{ $fh }}/css/owl.carousel.min.css"></noscript>

    {{-- defer in <head> before @vite so jQuery/mmenu run before the Vue module --}}
    <script src="{{ $fh }}/js/jquery-3.7.1.min.js" defer></script>
    <script src="{{ $fh }}/js/mmenu.min.js" defer></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @routes
    @inertiaHead
     {!! $settings->get('header_scripts') !!}
</head>
<body class="imas-theme-dark homepage-9 hp-6 homepage-1 mh">
@inertia
 {!! $settings->get('footer_scripts') !!}
</body>
</html>
