@extends('layouts.front')

@section('content')
@php
    $blog = $blog ?? [];
    $recentBlogs = $recentBlogs ?? [];
    $categories = $categories ?? [];
    $filters = $filters ?? ['q' => null, 'category_id' => null];
    $banner = $globals['media']['blog_show_banner'] ?? '';
    if (is_string($banner) && preg_match('#/default\.jpg(?:\?.*)?$#i', $banner)) {
        $banner = '';
    }
    $crumbs = [
        ['title' => front_trans('navBar.Home'), 'href' => route('home')],
        ['title' => front_trans('blogs.hub_title'), 'href' => route('blog.index')],
        ['title' => $blog['title'] ?? '', 'href' => null],
    ];
@endphp

<div class="imas-blog-v2 imas-blog-section-anchor">
    @include('front.components.inner-page-hero', [
        'pageTitle' => front_trans('blogs.blog_details'),
        'items' => $crumbs,
        'bannerImageUrl' => $banner,
    ])

    <main class="imas-blog-v2__page container">
        <section class="imas-blog-v2__main">
            <article class="imas-blog-show">
                @if (! empty($blog['image']))
                    <div class="imas-blog-show__media">
                        <img src="{{ $blog['image'] }}" alt="{{ $blog['title'] ?? '' }}" loading="eager">
                    </div>
                @endif
                <div class="imas-blog-show__content">
                    <header class="imas-blog-show-article-text__header">
                        <h1 class="imas-blog-show__title text-2xl font-bold text-start">{{ $blog['title'] ?? '' }}</h1>
                        <div class="imas-blog-show__meta text-md text-dim">
                            @if (! empty($blog['date']))
                                <span class="imas-blog-show__date">{{ $blog['date'] }}</span>
                            @endif
                            @if (! empty($blog['date']) && isset($blog['visits']))
                                <span class="imas-blog-show__meta-sep" aria-hidden="true">/</span>
                            @endif
                            @if (isset($blog['visits']))
                                <span class="imas-blog-show__views">
                                    <i class="fa fa-eye" aria-hidden="true"></i>
                                    {{ $blog['visits'] }}
                                </span>
                            @endif
                        </div>
                        @if (! empty($blog['category']))
                            <span class="imas-blog-show__category-label mb-4">{{ $blog['category']['name'] }}</span>
                        @endif
                    </header>
                    <div class="imas-blog-show-body text-base text-start">
                        {!! $blog['content'] ?? '' !!}
                    </div>
                </div>
            </article>
        </section>

        @include('front.components.blog-sidebar', [
            'filters' => $filters,
            'categories' => $categories,
            'recentBlogs' => $recentBlogs,
            'searchAction' => route('blog.index'),
        ])
    </main>
</div>
@endsection
