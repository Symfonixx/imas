@php($fh = asset('theme/findhouses'))
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
</head>
<body class="imas-theme-dark homepage-9 hp-6 homepage-1 mh">
@inertia
{{-- Minimal scripts for theme header / mobile nav (Bootstrap 4 + jQuery, aligned with Find Houses) --}}
<script src="{{ $fh }}/js/jquery-3.5.1.min.js"></script>
<script src="{{ $fh }}/js/owl.carousel.js"></script>
<script src="{{ $fh }}/js/tether.min.js"></script>
<script src="{{ $fh }}/js/bootstrap.min.js"></script>
<script src="{{ $fh }}/js/mmenu.min.js"></script>
<script src="{{ $fh }}/js/mmenu.js"></script>
</body>
</html>
