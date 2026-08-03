@extends('layouts.front')

@section('content')
@php
    $property = $property ?? [];
    $similarProperties = $similarProperties ?? [];
    $locale = $locale ?? app()->getLocale();
    $displayTitle = trim((string) ($property['title'] ?? ''));
    if ($displayTitle === '') {
        $displayTitle = (string) ($property['project_name'] ?? $property['project_code'] ?? 'Property');
    }
    $addressLine = property_location_line($property['location'] ?? null, $locale);
    $overviewHtml = front_localized($property['overview'] ?? null, $locale);
    $contentHtml = front_localized($property['content'] ?? null, $locale);
    $whyToBuyHtml = front_localized($property['why_to_buy'] ?? null, $locale);
    $typeLabel = front_localized($property['property_type']['name'] ?? null, $locale);
    $priceAmount = format_property_money(property_start_price($property), $locale);
    $banner = $globals['media']['property_show_banner'] ?? '';
    if (is_string($banner) && preg_match('#/default\.jpg(?:\?.*)?$#i', $banner)) {
        $banner = '';
    }
    $slides = $property['slides'] ?? [];
    $unitTypes = $property['unit_types'] ?? [];
    $hasMap = is_numeric($property['lat'] ?? null) && is_numeric($property['lng'] ?? null);
    $crumbs = [
        ['title' => front_trans('navBar.Home'), 'href' => route('home')],
        ['title' => front_trans('navBar.Buy Real Estate'), 'href' => route('property.index')],
        ['title' => $displayTitle, 'href' => null],
    ];
@endphp

<div class="inner-pages blog imas-property-show-page imas-blog-v2 imas-property-listings">
    @include('front.components.inner-page-hero', [
        'pageTitle' => front_trans('properties.proprty_details'),
        'items' => $crumbs,
        'bannerImageUrl' => $banner,
    ])

    <section class="single-proper blog details imas-property-show">
        <div class="container">
            <div class="row imas-property-show__content-row">
                <div class="col-lg-8 col-md-12 blog-pots">
                    <section class="headings-2 pt-0">
                        <div class="pro-wrapper imas-property-title-row">
                            <div class="detail-wrapper-body">
                                <div class="listing-title-bar text-start">
                                    @if (! empty($property['project_code']))
                                        <div class="mt-0">
                                            <span class="listing-address">
                                                {{ front_trans('property_show.project_id') }}: {{ $property['project_code'] }}
                                            </span>
                                        </div>
                                    @endif
                                    <h3>{{ $displayTitle }}</h3>
                                    @if ($addressLine !== '')
                                        <div class="mt-0">
                                            @if ($hasMap)
                                                <a href="#listing-location" class="listing-address">
                                                    <i class="fa fa-map-marker imas-address-marker" aria-hidden="true"></i>
                                                    <span>{{ $addressLine }}</span>
                                                </a>
                                            @else
                                                <span class="listing-address">
                                                    <i class="fa fa-map-marker imas-address-marker" aria-hidden="true"></i>
                                                    <span>{{ $addressLine }}</span>
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                    @if ($typeLabel !== '')
                                        <div class="imas-property-type-badge mt-2">{{ $typeLabel }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="single detail-wrapper ms-lg-auto">
                                <div class="detail-wrapper-body">
                                    <div class="listing-title-bar text-start text-lg-end">
                                        <h4 class="imas-price-heading">
                                            <span class="imas-price-heading__prefix">{{ front_trans('properties.price_from') }}</span>
                                            <span class="imas-price-heading__amount text-gold">{{ $priceAmount }}</span>
                                        </h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    @if (! empty($property['thumbnail_url']) || count($slides))
                        <div class="imas-property-gallery mb-4">
                            <img
                                src="{{ $property['thumbnail_url'] ?? ($slides[0]['image_url'] ?? asset('images/blank.png')) }}"
                                alt="{{ $property['thumbnail_alt'] ?? $displayTitle }}"
                                class="img-fluid w-100 rounded"
                            >
                            @if (count($slides))
                                <div class="row g-2 mt-2">
                                    @foreach (array_slice($slides, 0, 6) as $slide)
                                        <div class="col-4 col-md-2">
                                            <img src="{{ $slide['image_url'] }}" alt="{{ $slide['alt'] ?? $displayTitle }}" class="img-fluid rounded" loading="lazy">
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif

                    @if ($overviewHtml !== '')
                        <div class="blog-info details mb-30 text-start imas-property-show-panel">
                            <h5 class="imas-section-title mb-4">{{ front_trans('property_show.description') }}</h5>
                            <div class="imas-rich-content text-md">{!! $overviewHtml !!}</div>
                        </div>
                    @endif

                    @if (count($unitTypes))
                        <div class="blog-info details mb-30 text-start imas-property-show-panel">
                            <h5 class="imas-section-title mb-4">{{ front_trans('property_show.unit_types_title') }}</h5>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>{{ front_trans('property_show.col_rooms') }}</th>
                                            <th>{{ front_trans('property_show.col_area') }}</th>
                                            <th>{{ front_trans('property_show.col_price') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($unitTypes as $ut)
                                            <tr>
                                                <td>{{ front_localized($ut['name'] ?? '', $locale) }}</td>
                                                <td dir="ltr">
                                                    @if (isset($ut['min_area'], $ut['max_area']) && $ut['min_area'] != $ut['max_area'])
                                                        {{ $ut['min_area'] }}–{{ $ut['max_area'] }} m²
                                                    @else
                                                        {{ $ut['min_area'] ?? $ut['max_area'] ?? '—' }}
                                                        @if (isset($ut['min_area']) || isset($ut['max_area'])) m² @endif
                                                    @endif
                                                </td>
                                                <td>{{ format_property_money($ut['price'] ?? null, $locale) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    @if ($whyToBuyHtml !== '')
                        <div class="blog-info details mb-30 text-start imas-property-show-panel">
                            <h5 class="imas-section-title mb-4">{{ front_trans('property_show.why_to_buy') }}</h5>
                            <div class="imas-rich-content text-md">{!! $whyToBuyHtml !!}</div>
                        </div>
                    @endif

                    @if ($contentHtml !== '')
                        <div class="blog-info details mb-30 text-start imas-property-show-panel">
                            <h5 class="imas-section-title mb-4">{{ front_trans('property_show.details') }}</h5>
                            <div class="imas-rich-content text-md">{!! $contentHtml !!}</div>
                        </div>
                    @endif

                    @if ($hasMap)
                        <div id="listing-location" class="blog-info details mb-30">
                            <h5 class="imas-section-title mb-4">{{ front_trans('property_show.location') }}</h5>
                            <div class="ratio ratio-16x9">
                                <iframe
                                    src="https://maps.google.com/maps?q={{ urlencode($property['lat'].','.$property['lng']) }}&z=15&output=embed"
                                    loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade"
                                    title="{{ $displayTitle }}"
                                ></iframe>
                            </div>
                        </div>
                    @endif
                </div>

                <aside class="col-lg-4 col-md-12 car imas-blog-v2-sidebar imas-property-show__sidebar-col">
                    <div class="imas-property-show__contact-sticky">
                        @include('front.components.contact-form', [
                            'contactStoreUrl' => $contactStoreUrl ?? route('support.contact-us.store'),
                            'sourcePage' => $displayTitle,
                            'defaultSubject' => url()->current(),
                            'defaultMessage' => front_trans('property_show.default_inquiry_message'),
                            'hideSubject' => true,
                            'variant' => 'sidebar',
                        ])
                    </div>
                </aside>
            </div>
        </div>
    </section>

    @if (count($similarProperties))
        <section class="featured portfolio rec-pro disc">
            <div class="container-fluid">
                <div class="sec-title discover">
                    <h2><span>{{ front_trans('property_show.similar_properties') }}</span></h2>
                </div>
                <div class="row">
                    @foreach ($similarProperties as $sim)
                        @include('front.components.property-card', ['property' => $sim, 'columnClass' => 'col-lg-3 col-md-6 mb-4'])
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</div>
@endsection
