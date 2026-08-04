@extends('layouts.front')

@section('content')
@php
    $banner = $globals['media']['contact_us_banner'] ?? '';
    if (is_string($banner) && preg_match('#/default\.jpg(?:\?.*)?$#i', $banner)) {
        $banner = '';
    }
    $contact = $globals['contact'] ?? [];
    $settings = $settings ?? [];
    $socialDefs = [
        ['key' => 'facebook', 'label' => 'Facebook', 'icon' => 'fa fa-facebook'],
        ['key' => 'twitter', 'label' => 'Twitter', 'icon' => 'fa fa-twitter'],
        ['key' => 'instagram', 'label' => 'Instagram', 'icon' => 'fab fa-instagram'],
        ['key' => 'youtube', 'label' => 'YouTube', 'icon' => 'fa fa-youtube'],
        ['key' => 'tiktok', 'label' => 'TikTok', 'icon' => 'fab fa-tiktok'],
    ];
    $socialLinks = [];
    foreach ($socialDefs as $def) {
        $href = trim((string) ($settings[$def['key']] ?? ($globals['social'][$def['key']] ?? '')));
        if ($href !== '') {
            $socialLinks[] = array_merge($def, ['href' => $href]);
        }
    }
    $rawPhone = trim((string) ($contact['phone'] ?? $settings['contact_phone'] ?? ''));
    $phoneDisplay = $rawPhone !== '' ? (format_turkish_phone($rawPhone) ?: $rawPhone) : '';
    $phoneHref = whatsapp_contact_href($globals['social']['whatsapp'] ?? null, $rawPhone);
    $crumbs = [
        ['title' => front_trans('navBar.Home'), 'href' => route('home')],
        ['title' => front_trans('contact_us.title'), 'href' => null],
    ];
@endphp

<div class="inner-pages imas-contact-page">
    @include('front.components.inner-page-hero', [
        'pageTitle' => front_trans('contact_us.title'),
        'items' => $crumbs,
        'bannerImageUrl' => $banner,
    ])

    <section class="contact-us imas-contact-page__section">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-8 col-md-12">
                    <div class="imas-contact-page__panel imas-contact-page__panel--form">
                        @include('front.components.contact-form', [
                            'contactStoreUrl' => $contactStoreUrl ?? route('support.contact-us.store'),
                            'sourcePage' => front_trans('contact_us.title'),
                        ])
                    </div>
                </div>
                <div class="col-lg-4 col-md-12">
                    <div class="imas-contact-page__panel imas-contact-page__panel--details">
                        <div class="call-info imas-contact-page__details">
                            <h3 class="imas-contact-page__heading text-xl font-semibold text-start">
                                {{ front_trans('contact_us.contact_details') }}
                            </h3>
                            <p class="imas-contact-page__intro text-card-excerpt text-dim mb-5 text-start">
                                {{ front_trans('contact_us.Please_find_below_contact_details_and_contact_us_today') }}
                            </p>
                            <div class="imas-contact-page__head-office mb-4 text-start">
                                <p class="imas-contact-page__head-office-title mb-1">
                                    <span class="imas-contact-page__head-office-brand">IMAS GLOBAL</span>
                                </p>
                            </div>
                            <ul>
                                @if (! empty($contact['address']))
                                    <li>
                                        <div class="info text-start">
                                            <i class="fa fa-map-marker m-end" aria-hidden="true"></i>
                                            <p class="in-p">{{ $contact['address'] }}</p>
                                        </div>
                                    </li>
                                @endif
                                @if ($phoneDisplay)
                                    <li class="imas-contact-phone">
                                        <div class="info">
                                            <i class="fa fa-phone m-end" aria-hidden="true"></i>
                                            <p class="in-p in-p--phone" dir="ltr">
                                                <a href="{{ $phoneHref }}" target="_blank" rel="noopener noreferrer">{{ $phoneDisplay }}</a>
                                            </p>
                                        </div>
                                    </li>
                                @endif
                                @if (! empty($contact['email']))
                                    <li class="imas-contact-email">
                                        <div class="info">
                                            <i class="fa fa-envelope m-end" aria-hidden="true"></i>
                                            <p class="in-p ti">
                                                <a href="mailto:{{ $contact['email'] }}">{{ $contact['email'] }}</a>
                                            </p>
                                        </div>
                                    </li>
                                @endif
                            </ul>
                            @if (count($socialLinks))
                                <h4 class="imas-contact-page__social-title text-lg font-semibold mt-4 mb-3 text-start">
                                    {{ front_trans('contact_us.follow_us') }}
                                </h4>
                                <ul class="netsocials d-flex flex-wrap">
                                    @foreach ($socialLinks as $item)
                                        <li>
                                            <a href="{{ $item['href'] }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $item['label'] }}">
                                                <i class="{{ $item['icon'] }}" aria-hidden="true"></i>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
