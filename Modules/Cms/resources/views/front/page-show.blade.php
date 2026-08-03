@extends('layouts.front')

@section('content')
@php
    $pageData = $page ?? [];
    $banner = $pageData['image'] ?? '';
    if (is_string($banner) && preg_match('#/default\.jpg(?:\?.*)?$#i', $banner)) {
        $banner = '';
    }
    $crumbs = [
        ['title' => front_trans('navBar.Home'), 'href' => route('home')],
        ['title' => $pageData['title'] ?? '', 'href' => null],
    ];
@endphp

<div class="imas-blog-v2">
    @include('front.components.inner-page-hero', [
        'pageTitle' => $pageData['title'] ?? '',
        'items' => $crumbs,
        'bannerImageUrl' => $banner,
    ])

    <main class="imas-cms-page-show__page container">
        <article class="imas-blog-show imas-cms-page-show">
            <div class="imas-blog-show__content">
                <div class="imas-blog-show-body imas-cms-page-show__body text-base text-start">
                    {!! $pageData['content'] ?? '' !!}
                </div>
            </div>
        </article>
    </main>
</div>
@endsection
