@extends('layouts.front')

@section('content')
@php
    $properties = $properties ?? null;
    $items = $properties?->items() ?? [];
    $pageTitle = $title ?? front_trans('properties.favorite_properties');
    $banner = $globals['media']['property_show_banner'] ?? '';
    if (is_string($banner) && preg_match('#/default\.jpg(?:\?.*)?$#i', $banner)) {
        $banner = '';
    }
    $crumbs = [
        ['title' => front_trans('navBar.Home'), 'href' => route('home')],
        ['title' => $pageTitle, 'href' => null],
    ];
    $total = $properties?->total() ?? count($items);
@endphp

<div class="imas-blog-v2 imas-property-listings imas-blog-section-anchor">
    @include('front.components.inner-page-hero', [
        'pageTitle' => $pageTitle,
        'items' => $crumbs,
        'bannerImageUrl' => $banner,
    ])

    <main class="imas-blog-v2__page">
        <section class="imas-blog-v2__main">
            @if (count($items) > 0)
                <p class="imas-property-listings__count text-dim">
                    {{ str_replace(':count', (string) $total, front_trans('listing_page.results_count')) }}
                </p>
                <div class="imas-property-listings__grid row">
                    @foreach ($items as $property)
                        @include('front.components.property-card', ['property' => $property, 'columnClass' => 'col-lg-6 col-md-6 mb-4'])
                    @endforeach
                </div>
            @else
                <p class="imas-blog-v2__empty text-dim">{{ front_trans('properties.favorite_properties_empty') }}</p>
            @endif

            @include('front.components.pagination', ['paginator' => $properties])
        </section>

        <aside class="imas-blog-v2-sidebar">
            <div class="imas-favorites-aside-sticky">
                @include('front.components.contact-form', [
                    'contactStoreUrl' => $contactStoreUrl ?? route('support.contact-us.store'),
                    'sourcePage' => $pageTitle,
                    'defaultSubject' => $pageTitle,
                    'variant' => 'sidebar',
                ])
            </div>
        </aside>
    </main>
</div>
@endsection
