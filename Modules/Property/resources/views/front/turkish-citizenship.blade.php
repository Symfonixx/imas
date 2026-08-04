@extends('layouts.front')

@section('content')
@php
    $turkishCitizenship = $turkishCitizenship ?? [];
    $citizenshipProperties = $citizenshipProperties ?? [];
    $contentHtml = $turkishCitizenship['content'] ?? '';
    $youtubeEmbed = trim((string) ($turkishCitizenship['youtube_embed'] ?? ''));
    $banner = $turkishCitizenship['banner_url'] ?? '';
    if (is_string($banner) && preg_match('#/default\.jpg(?:\?.*)?$#i', $banner)) {
        $banner = '';
    }
    $title = front_trans('navBar.Turkish Citizenship');
    $crumbs = [
        ['title' => front_trans('navBar.Home'), 'href' => route('home')],
        ['title' => $title, 'href' => null],
    ];
@endphp

<div class="inner-pages imas-tc-page-root">
    @include('front.components.inner-page-hero', [
        'pageTitle' => $title,
        'items' => $crumbs,
        'bannerImageUrl' => $banner,
    ])

    <section class="blog blog-section bg-white pt-3 pb-5 imas-tc-page">
        <div class="container">
            <div class="row imas-tc-page__content-row">
                <div class="col-lg-8 col-md-12">
                    <h2 class="text-2xl font-bold mb-4 text-start">{{ $title }}</h2>
                    <div class="blog-pots imas-tc-page-content">
                        @if ($contentHtml)
                            <div class="imas-tc-content">{!! $contentHtml !!}</div>
                        @endif
                        @if ($youtubeEmbed)
                            <div class="imas-tc-video ratio ratio-16x9 mb-4 mt-4 w-100">{!! $youtubeEmbed !!}</div>
                        @endif
                        @if (! $contentHtml && ! $youtubeEmbed)
                            <p class="text-muted">{{ front_trans('Turkish citizenship page has no published content yet.') }}</p>
                        @endif
                    </div>
                </div>
                <aside class="col-lg-4 col-md-12 car imas-tc-page__sidebar-col">
                    <div class="imas-tc-page__contact-sticky">
                        @include('front.components.contact-form', [
                            'contactStoreUrl' => $contactStoreUrl ?? route('support.contact-us.store'),
                            'sourcePage' => $title,
                            'defaultSubject' => $title,
                            'hideTitle' => false,
                            'variant' => 'sidebar',
                        ])
                    </div>
                </aside>
            </div>
        </div>
    </section>

    @if (count($citizenshipProperties))
        <section class="featured portfolio rec-pro disc">
            <div class="container-fluid">
                <div class="sec-title discover">
                    <h2><span>{{ front_trans('suitable_properties_for_turkish_citizenship_by_citizenship_program') }}</span></h2>
                </div>
                <div class="row">
                    @foreach ($citizenshipProperties as $property)
                        @include('front.components.property-card', ['property' => $property, 'columnClass' => 'col-lg-3 col-md-6 mb-4'])
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</div>
@endsection
