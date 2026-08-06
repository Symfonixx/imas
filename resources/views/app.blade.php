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

    {{-- Document SEO in the first HTML response (View Page Source). inertia="" lets Vue Head replace these on SPA navigations. --}}
    <title inertia>{{ $seoTitle }}</title>
    @if ($seoDescription !== '')
        <meta inertia="description" name="description" content="{{ $seoDescription }}">
        <meta inertia="og:description" property="og:description" content="{{ $seoDescription }}">
    @endif
    @if ($seoKeywords !== '')
        <meta inertia="keywords" name="keywords" content="{{ $seoKeywords }}">
    @endif
    <meta inertia="og:title" property="og:title" content="{{ $seoTitle }}">
    <meta inertia="og:type" property="og:type" content="{{ $seoOgType }}">
    @if ($seoOgImage !== '')
        <meta inertia="og:image" property="og:image" content="{{ $seoOgImage }}">
    @endif
    @if ($seoCanonical !== '')
        <link inertia="canonical" rel="canonical" href="{{ $seoCanonical }}">
        <meta inertia="og:url" property="og:url" content="{{ $seoCanonical }}">
    @endif
    @if ($seoRobots !== '')
        <meta inertia="robots" name="robots" content="{{ $seoRobots }}">
    @endif
    <meta inertia="twitter:card" name="twitter:card" content="{{ $seoOgImage !== '' ? 'summary_large_image' : 'summary' }}">
    <meta inertia="twitter:title" name="twitter:title" content="{{ $seoTitle }}">
    @if ($seoDescription !== '')
        <meta inertia="twitter:description" name="twitter:description" content="{{ $seoDescription }}">
    @endif
    @if ($seoOgImage !== '')
        <meta inertia="twitter:image" name="twitter:image" content="{{ $seoOgImage }}">
    @endif

    {{-- Structured data. Values are encoded by SeoDocumentService with JSON_HEX_TAG|JSON_HEX_AMP. --}}
    @foreach ($seoJsonLd as $seoJsonLdKey => $seoJsonLdBlock)
        <script inertia="{{ $seoJsonLdKey }}" type="application/ld+json">{!! $seoJsonLdBlock !!}</script>
    @endforeach

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

    {{-- Find Houses theme styles (order matches theme/findhouses/index.html) --}}
    <link rel="stylesheet" href="{{ $fh }}/css/jquery-ui.css">
    <link rel="stylesheet" href="{{ $fh }}/font/flaticon.css">
    <link rel="stylesheet" href="{{ $fh }}/css/fontawesome-all.min.css">
    <link rel="stylesheet" href="{{ $fh }}/css/fontawesome-5-all.min.css">
    <link rel="stylesheet" href="{{ $fh }}/css/font-awesome.min.css">
    <link rel="stylesheet" href="{{ $fh }}/css/search.css">
    <link rel="stylesheet" href="{{ $fh }}/css/animate.css">
    <link rel="stylesheet" href="{{ $fh }}/css/aos.css">
    <link rel="stylesheet" href="{{ $fh }}/css/aos2.css">
    <link rel="stylesheet" href="{{ $fh }}/css/magnific-popup.css">
    <link rel="stylesheet" href="{{ $fh }}/css/lightcase.css">
    <link rel="stylesheet" href="{{ $fh }}/css/owl.carousel.min.css">
    <link rel="stylesheet" href="{{ $fh }}/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ $fh }}/css/menu.css">
    <link rel="stylesheet" href="{{ $fh }}/css/slick.css">
    <link rel="stylesheet" href="{{ $fh }}/css/styles.css">
    <link rel="stylesheet" href="{{ $fh }}/css/maps.css">
    <link rel="stylesheet" href="{{ $fh }}/css/colors/pink.css" id="color">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @routes
    @inertiaHead
     {!! $settings->get('header_scripts') !!}
</head>
<body class="imas-theme-dark homepage-9 hp-6 homepage-1 mh">
@inertia
{{-- Minimal scripts for theme header / mobile nav (Bootstrap 4 + jQuery, aligned with Find Houses) --}}
<script src="{{ $fh }}/js/jquery-3.5.1.min.js"></script>
<script src="{{ $fh }}/js/owl.carousel.js"></script>
<script src="{{ $fh }}/js/tether.min.js"></script>
<script src="{{ $fh }}/js/bootstrap.min.js"></script>
<script src="{{ $fh }}/js/mmenu.min.js"></script>
{{-- Theme mmenu.js clones #header/#navigation on DOM ready and breaks Vue SSR hydration; mobile menu is initialized in UserNavbar.vue --}}
 {!! $settings->get('footer_scripts') !!}
</body>
</html>
