@extends('layouts.front')

@section('content')
@php
    $aboutUs = $aboutUs ?? [];
    $featuredProperties = $featuredProperties ?? [];
    $contentHtml = $aboutUs['content'] ?? '';
    $heroYoutube = trim((string) ($aboutUs['youtube_embed'] ?? ''));
    $banner = $globals['media']['about_us_banner'] ?? '';
    if (is_string($banner) && preg_match('#/default\.jpg(?:\?.*)?$#i', $banner)) {
        $banner = '';
    }
    $pageHeadingTitle = front_trans('about_us.title');
    $crumbs = [
        ['title' => front_trans('navBar.Home'), 'href' => route('home')],
        ['title' => $pageHeadingTitle, 'href' => null],
    ];
@endphp

<div class="inner-pages imas-about-page">
    @include('front.components.inner-page-hero', [
        'pageTitle' => $pageHeadingTitle,
        'items' => $crumbs,
        'bannerImageUrl' => $banner,
        'bannerVideoEmbed' => $heroYoutube,
    ])

    <main class="imas-about-page__page imas-blog-v2__page container {{ count($featuredProperties) ? 'imas-about-page__page--with-sidebar' : '' }}">
        <section class="imas-about-page__main">
            @if ($contentHtml)
                <article class="imas-blog-show imas-cms-page-show">
                    <div class="imas-blog-show__content">
                        <div class="imas-blog-show-body imas-cms-page-show__body text-base text-start">
                            {!! $contentHtml !!}
                        </div>
                    </div>
                </article>
            @else
                <p class="imas-about-page__empty text-muted text-base">{{ front_trans('about_us.no_content') }}</p>
            @endif
        </section>

        @if (count($featuredProperties))
            <aside class="imas-blog-v2-sidebar">
                <div class="imas-blog-v2-sidebar__box">
                    <h4 class="imas-blog-v2-sidebar__heading text-start">{{ front_trans('properties.featured_properties') }}</h4>
                    <div class="row">
                        @foreach (array_slice($featuredProperties, 0, 4) as $property)
                            @include('front.components.property-card', ['property' => $property, 'columnClass' => 'col-12 mb-3'])
                        @endforeach
                    </div>
                </div>
            </aside>
        @endif
    </main>
</div>
@endsection
