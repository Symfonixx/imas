@php
    $filters = $filters ?? ['q' => null, 'category_id' => null];
    $categories = $categories ?? [];
    $recentBlogs = $recentBlogs ?? [];
    $searchAction = $searchAction ?? route('blog.index');
@endphp
<aside class="imas-blog-v2-sidebar">
    <div class="imas-blog-v2-sidebar__box">
        <h4 class="imas-blog-v2-sidebar__heading text-start">{{ front_trans('blogs.search') }}</h4>
        <form action="{{ $searchAction }}" method="get" class="imas-blog-v2-sidebar__search">
            @if (! empty($filters['category_id']))
                <input type="hidden" name="category_id" value="{{ $filters['category_id'] }}">
            @endif
            <input
                type="text"
                name="q"
                class="imas-blog-v2-sidebar__search-input"
                placeholder="{{ front_trans('blogs.search_placeholder') }}"
                value="{{ $filters['q'] ?? '' }}"
                autocomplete="off"
            >
            <button type="submit" class="imas-blog-v2-sidebar__search-btn" aria-label="{{ front_trans('blogs.search') }}">
                <i class="fa fa-search" aria-hidden="true"></i>
            </button>
        </form>
    </div>

    <div class="imas-blog-v2-sidebar__box">
        <h4 class="imas-blog-v2-sidebar__heading text-start">{{ front_trans('blogs.categories') }}</h4>
        <ul class="imas-blog-v2-sidebar__cat-list">
            <li>
                <a
                    href="{{ route('blog.index') }}"
                    class="imas-blog-v2-sidebar__cat-link {{ empty($filters['category_id']) ? 'is-active' : '' }}"
                >
                    {{ front_trans('blogs.all_categories') }}
                </a>
            </li>
            @foreach ($categories as $c)
                <li>
                    <a
                        href="{{ route('blog.index', ['category_id' => $c['id']]) }}"
                        class="imas-blog-v2-sidebar__cat-link {{ (int) ($filters['category_id'] ?? 0) === (int) $c['id'] ? 'is-active' : '' }}"
                    >
                        {{ $c['name'] }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>

    @if (count($recentBlogs))
        <div class="imas-blog-v2-sidebar__box">
            <h4 class="imas-blog-v2-sidebar__heading text-start">{{ front_trans('blogs.recent_posts') }}</h4>
            <div class="imas-blog-v2-sidebar__recent">
                @foreach ($recentBlogs as $r)
                    <a href="{{ $r['url'] }}" class="imas-blog-v2-sidebar__recent-item">
                        <img src="{{ $r['image'] }}" alt="{{ $r['title'] }}" loading="lazy">
                        <div>
                            <div class="imas-blog-v2-sidebar__recent-title">{{ $r['title'] }}</div>
                            @if (! empty($r['date']))
                                <div class="imas-blog-v2-sidebar__recent-date text-dim text-start">{{ $r['date'] }}</div>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</aside>
