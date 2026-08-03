@extends('layouts.front')

@section('content')
@php
    $properties = $properties ?? null;
    $filters = $filters ?? [];
    $sort = $sort ?? 'price_asc';
    $propertyTypes = $propertyTypes ?? [];
    $cities = $cities ?? [];
    $recentProperties = $recentProperties ?? [];
    $featuredProperties = $featuredProperties ?? [];
    $pageTitle = $title ?? front_trans('properties.property_Listings');
    $banner = $globals['media']['property_show_banner'] ?? '';
    if (is_string($banner) && preg_match('#/default\.jpg(?:\?.*)?$#i', $banner)) {
        $banner = '';
    }
    $items = $properties?->items() ?? [];
    $crumbs = [
        ['title' => front_trans('navBar.Home'), 'href' => route('home')],
        ['title' => front_trans('navBar.Buy Real Estate'), 'href' => null],
    ];
@endphp

<div class="imas-blog-v2 imas-property-listings imas-blog-section-anchor">
    @include('front.components.inner-page-hero', [
        'pageTitle' => $pageTitle,
        'items' => $crumbs,
        'bannerImageUrl' => $banner,
    ])

    <main class="imas-blog-v2__page">
        <section class="imas-blog-v2__main">
            <form method="get" action="{{ route('property.index') }}" class="imas-property-listings__toolbar mb-4">
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <input type="text" name="q" class="form-control" placeholder="{{ front_trans('Keyword') }}" value="{{ $filters['q'] ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <select name="property_type_id" class="form-control">
                            <option value="">{{ front_trans('Property Type') }}</option>
                            @foreach ($propertyTypes as $t)
                                <option value="{{ $t['id'] }}" @selected((int) ($filters['property_type_id'] ?? 0) === (int) $t['id'])>
                                    {{ front_localized($t['name'] ?? '') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="sort" class="form-control">
                            <option value="price_asc" @selected($sort === 'price_asc')>{{ front_trans('Price: Low to High') }}</option>
                            <option value="price_desc" @selected($sort === 'price_desc')>{{ front_trans('Price: High to Low') }}</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">{{ front_trans('Search') }}</button>
                    </div>
                </div>
                @foreach ((array) ($filters['location_id'] ?? []) as $locId)
                    <input type="hidden" name="location_id[]" value="{{ $locId }}">
                @endforeach
            </form>

            @if (count($items) > 0)
                <div class="imas-property-listings__grid row">
                    @foreach ($items as $property)
                        @include('front.components.property-card', ['property' => $property, 'columnClass' => 'col-lg-6 col-md-6 mb-4'])
                    @endforeach
                </div>
            @else
                <p class="imas-blog-v2__empty text-dim">
                    {{ str_replace(':count', '0', front_trans('listing_page.results_count')) }}
                </p>
            @endif

            @include('front.components.pagination', ['paginator' => $properties])
        </section>

        <aside class="imas-blog-v2-sidebar">
            <div class="imas-blog-v2-sidebar__box">
                <h4 class="imas-blog-v2-sidebar__heading text-start">{{ front_trans('Search') }}</h4>
                <form action="{{ route('property.index') }}" method="get" class="imas-blog-v2-sidebar__search">
                    <input type="text" name="q" class="imas-blog-v2-sidebar__search-input" value="{{ $filters['q'] ?? '' }}" placeholder="{{ front_trans('Keyword') }}">
                    <button type="submit" class="imas-blog-v2-sidebar__search-btn" aria-label="{{ front_trans('Search') }}">
                        <i class="fa fa-search" aria-hidden="true"></i>
                    </button>
                </form>
            </div>
            @if (count($featuredProperties))
                <div class="imas-blog-v2-sidebar__box">
                    <h4 class="imas-blog-v2-sidebar__heading text-start">{{ front_trans('properties.featured_properties') }}</h4>
                    @foreach (array_slice($featuredProperties, 0, 3) as $property)
                        @include('front.components.property-card', ['property' => $property, 'columnClass' => 'col-12 mb-3'])
                    @endforeach
                </div>
            @endif
            @if (count($recentProperties))
                <div class="imas-blog-v2-sidebar__box">
                    <h4 class="imas-blog-v2-sidebar__heading text-start">{{ front_trans('properties.recent') }}</h4>
                    @foreach (array_slice($recentProperties, 0, 3) as $property)
                        @include('front.components.property-card', ['property' => $property, 'columnClass' => 'col-12 mb-3'])
                    @endforeach
                </div>
            @endif
        </aside>
    </main>
</div>
@endsection
