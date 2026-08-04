@php
    $fh = $theme_url ?? asset('theme/findhouses');
    $feedSiteName = trim((string) ($appName ?? config('app.name')));
    $headerScripts = $settings['header_scripts'] ?? '';
    $footerScripts = $settings['footer_scripts'] ?? '';
@endphp
<!DOCTYPE html>
<html lang="{{ $locale ?? app()->getLocale() }}" dir="{{ $text_direction ?? (app()->getLocale() === 'ar' ? 'rtl' : 'ltr') }}">
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

    <style>
    [x-cloak] { display: none !important; }
</style>
@vite(['resources/css/app.css', 'resources/js/front.js'])
    @routes
    @include('partials.seo-head')
    {!! $headerScripts !!}
    @stack('head')
</head>
<body class="imas-theme-dark homepage-9 hp-6 homepage-1 mh" @stack('body-attrs')>
<div id="wrapper" class="imas-theme-dark">
    @include('front.partials.top-bar')
    @include('front.partials.navbar')
    <div class="clearfix"></div>
    @yield('content')
    @include('front.partials.footer')
    @include('front.partials.floating-contact')
    @include('front.partials.auth-modal')
</div>

<script src="{{ $fh }}/js/jquery-3.5.1.min.js"></script>
<script src="{{ $fh }}/js/owl.carousel.js"></script>
<script src="{{ $fh }}/js/tether.min.js"></script>
<script src="{{ $fh }}/js/bootstrap.min.js"></script>
<script src="{{ $fh }}/js/mmenu.min.js"></script>
{!! $footerScripts !!}
@stack('scripts')
</body>
</html>
