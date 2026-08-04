@extends('layouts.front')

@section('content')
@php
    $slides = $slides ?? [];
    $propertyTypes = $propertyTypes ?? [];
    $cities = $cities ?? [];
    $featuredProperties = $featuredProperties ?? [];
    $recommendedProperties = $recommendedProperties ?? [];
    $corporateServices = $corporateServices ?? [];
    $testimonials = $testimonials ?? [];
    $articles = $articles ?? [];
    $globals = $globals ?? [];
    $aboutSummary = front_strip_html($globals['about']['summary'] ?? ($globals['seo']['about_us'] ?? ''));
    $tcSummary = front_strip_html($globals['turkish_citizenship']['summary'] ?? ($globals['seo']['turkish_citizenship'] ?? ''));
    $tcBanner = $globals['turkish_citizenship']['banner_url'] ?? ($globals['media']['turkish_citizenship_banner'] ?? '');
    $firstSlide = $slides[0] ?? null;
    $heroTitle = $firstSlide['title'] ?? ($welcomeTitle ?? front_trans('navBar.Home'));
    $heroSubtitle = $firstSlide['description'] ?? ($welcomeSubtitle ?? '');
    $heroBg = $firstSlide['image'] ?? '';
@endphp

<section
    id="hero-area"
    class="parallax-searchs home15 overlay thome-6 thome-1 {{ count($slides) ? 'imas-hero-slider' : '' }}"
    @if ($heroBg) style="background-image: url('{{ $heroBg }}'); background-size: cover; background-position: center;" @endif
>
    @if (count($slides))
        <div class="imas-hero-slider__layers" aria-hidden="true">
            @foreach ($slides as $index => $slide)
                <div
                    class="imas-hero-slider__slide {{ $index === 0 ? 'imas-hero-slider__slide--active' : '' }}"
                    style="background-image: url('{{ $slide['image'] }}')"
                ></div>
            @endforeach
            <div class="imas-hero-slider__scrim"></div>
        </div>
    @endif
    <div class="hero-main">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="hero-inner">
                        <div class="imas-hero-copy">
                            <div class="welcome-text">
                                <h1 class="h1 imas-hero-title imas-hero-title--static">{{ $heroTitle }}</h1>
                                @if ($heroSubtitle)
                                    <p class="mt-4 imas-hero-subtitle">{{ $heroSubtitle }}</p>
                                @endif
                                @if (! empty($firstSlide['link']))
                                    <a href="{{ $firstSlide['link'] }}" class="imas-hero-action" target="_blank" rel="noopener noreferrer">
                                        {{ front_trans('turkishCitizenship.discover_more') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="imas-hero-filter-shell">
            <div class="imas-hero-filter">
                <div class="banner-search-wrap imas-hero-property-search">
                    <form class="tab-content" method="get" action="{{ route('property.index') }}">
                        <div class="tab-pane fade show active">
                            <div class="rld-main-search">
                                <div class="imas-hero-search-row">
                                    <div class="imas-hero-search-fields">
                                        <div class="rld-single-select imas-hero-city-cell">
                                            <select name="location_id[]" class="select single-select wide">
                                                <option value="">{{ front_trans('City') }}</option>
                                                @foreach ($cities as $city)
                                                    <option value="{{ $city['id'] }}">{{ front_localized($city['name'] ?? '') }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="rld-single-select ml-22">
                                            <select name="property_type_id" class="select single-select wide">
                                                <option value="">{{ front_trans('Property Type') }}</option>
                                                @foreach ($propertyTypes as $t)
                                                    <option value="{{ $t['id'] }}">{{ front_localized($t['name'] ?? '') }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="rld-single-input">
                                            <input type="text" name="q" placeholder="{{ front_trans('Keyword') }}" value="">
                                        </div>
                                        <div class="imas-hero-search-submit">
                                            <button type="submit" class="btn btn-yellow">{{ front_trans('Search Now') }}</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

@if (count($featuredProperties))
<section class="featured portfolio bg-white-2 imas-home-section">
    <div class="container">
        <div class="sec-title">
            <h2>{{ front_trans('properties.featured_properties') }}</h2>
            <p>{{ front_trans('properties.we_provide_full_service_at_every_step') }}</p>
        </div>
        <div class="row portfolio-items">
            @foreach ($featuredProperties as $property)
                @include('front.components.property-card', ['property' => $property, 'columnClass' => 'col-lg-4 col-md-6 col-xs-12 mb-4'])
            @endforeach
        </div>
        <div class="text-center mt-3">
            <a href="{{ route('property.index') }}" class="btn btn-primary">{{ front_trans('global.view_more') }}</a>
        </div>
    </div>
</section>
@endif

@if ($tcSummary !== '')
<section class="imas-tc-overview" aria-label="{{ front_trans('navBar.Turkish Citizenship') }}">
    @if ($tcBanner && ! preg_match('#/default\.jpg#i', $tcBanner))
        <div class="imas-tc-overview__bg" style="background-image: url('{{ $tcBanner }}')" aria-hidden="true"></div>
    @endif
    <div class="imas-tc-overview__overlay" aria-hidden="true"></div>
    <div class="container imas-tc-overview__inner">
        <div class="imas-tc-overview__panel">
            <h2 class="text-center text-2xl font-bold mb-3">{{ front_trans('navBar.Turkish Citizenship') }}</h2>
            <p class="imas-tc-overview__text">{{ $tcSummary }}</p>
            <a href="{{ route('turkish-citizenship') }}" class="imas-tc-overview__cta">
                <span>{{ front_trans('turkishCitizenship.discover_more') }}</span>
            </a>
        </div>
    </div>
</section>
@endif

@if ($aboutSummary !== '')
<section class="imas-about-overview" aria-label="{{ front_trans('about_us.title') }}">
    <div class="container imas-about-overview__inner">
        <div class="imas-about-overview__panel">
            <h2 class="imas-about-overview__title">
                <span class="imas-about-overview__title-primary">{{ front_trans('aboutUs.overview_title_primary') }}</span>
                <span class="imas-about-overview__title-accent">{{ front_trans('aboutUs.overview_title_accent') }}</span>
            </h2>
            <hr class="imas-about-overview__divider">
            <p class="imas-about-overview__text">{{ $aboutSummary }}</p>
            <a href="{{ route('about-us') }}" class="imas-about-overview__cta">
                <span>{{ front_trans('aboutUs.explore_more') }}</span>
            </a>
        </div>
    </div>
</section>
@endif

@if (count($testimonials))
<section class="home-testimonials testimonials bg-white-2 rec-pro">
    <div class="container-fluid">
        <div class="sec-title">
            <h2><span>{{ front_trans('testimonials.title') }}</span></h2>
            <p>{{ front_trans('testimonials.description') }}</p>
        </div>
        <div class="row">
            @foreach ($testimonials as $item)
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="singleJobClinet bg-gray">
                        <p class="quote">{!! $item['quote'] !!}</p>
                        <div class="detailJC">
                            <span><img src="{{ $item['avatar'] }}" alt="{{ $item['name'] }}"></span>
                            <h5>
                                @if (! empty($item['link']))
                                    <a href="{{ $item['link'] }}" rel="noopener noreferrer" target="_blank">{{ $item['name'] }}</a>
                                @else
                                    {{ $item['name'] }}
                                @endif
                            </h5>
                            <p>{{ $item['position'] ?: ($item['client'] ?? '') }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

@if (count($articles))
<section class="blog-section bg-white-2 imas-home-section">
    <div class="container">
        <div class="sec-title">
            <h2>{{ front_trans('articles.title') }}</h2>
            <p>{{ front_trans('articles.description') }}</p>
        </div>
        <div class="news-wrap">
            <div class="row">
                @foreach ($articles as $article)
                    <div class="col-lg-4 col-md-6 d-flex mb-4">
                        @include('front.components.article-card', [
                            'article' => $article,
                            'readMoreLabel' => front_trans('articles.read_more'),
                        ])
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif

@if (count($corporateServices))
<section class="home-services">
    <div class="container">
        <section class="how-it-works bg-white rec-pro">
            <div class="container-fluid">
                <div class="sec-title">
                    <h2>{{ front_trans('services.title') }}</h2>
                    <p>{{ front_trans('services.description') }}</p>
                </div>
                <div class="row service-1">
                    @foreach ($corporateServices as $service)
                        <article class="col-lg-4 col-md-6 col-xs-12 serv mb-4">
                            <div class="serv-flex">
                                <div class="art-1 img-13 corporate-service-art">
                                    @if (! empty($service['image']))
                                        <img class="corporate-service-img" src="{{ $service['image'] }}" alt="{{ $service['title'] }}" loading="lazy">
                                    @endif
                                    <h3>{{ $service['title'] }}</h3>
                                </div>
                                <div class="service-text-p">
                                    <p class="text-center">{{ $service['description'] }}</p>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    </div>
</section>
@endif

@if (count($recommendedProperties))
<section class="featured portfolio rec-pro disc imas-home-section">
    <div class="container-fluid">
        <div class="sec-title discover">
            <h2><span>{{ front_trans('properties.title') }}</span></h2>
            <p>{{ front_trans('properties.we_provide_full_service_at_every_step') }}</p>
        </div>
        <div class="portfolio col-xl-12">
            <div class="row">
                @foreach ($recommendedProperties as $property)
                    @include('front.components.property-card', ['property' => $property, 'columnClass' => 'col-lg-3 col-md-6 col-xs-12 mb-4'])
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif
@endsection
