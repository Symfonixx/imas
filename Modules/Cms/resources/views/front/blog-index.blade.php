@extends('layouts.front')

@section('content')
@php
    $blogs = $blogs ?? null;
    $items = $blogs?->items() ?? [];
    $recentBlogs = $recentBlogs ?? [];
    $categories = $categories ?? [];
    $filters = $filters ?? ['q' => null, 'category_id' => null];
    $banner = $globals['media']['blog_show_banner'] ?? '';
    if (is_string($banner) && preg_match('#/default\.jpg(?:\?.*)?$#i', $banner)) {
        $banner = '';
    }
    $crumbs = [
        ['title' => front_trans('navBar.Home'), 'href' => route('home')],
        ['title' => front_trans('blogs.hub_title'), 'href' => null],
    ];
@endphp

<div class="imas-blog-v2 imas-blog-section-anchor">
    @include('front.components.inner-page-hero', [
        'pageTitle' => front_trans('blogs.hub_title'),
        'items' => $crumbs,
        'bannerImageUrl' => $banner,
    ])

    <main class="imas-blog-v2__page container">
        <section class="imas-blog-v2__main">
            @if (count($items) > 0)
                <div class="imas-blog-v2__grid row">
                    @foreach ($items as $post)
                        <div class="col-lg-6 col-md-6 d-flex mb-4">
                            @include('front.components.article-card', [
                                'article' => $post,
                                'readMoreLabel' => front_trans('articles.read_more'),
                            ])
                        </div>
                    @endforeach
                </div>
            @else
                <p class="imas-blog-v2__empty text-dim text-start">{{ front_trans('blogs.no_posts') }}</p>
            @endif

            @include('front.components.pagination', ['paginator' => $blogs])
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
