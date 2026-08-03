@php
    /** @var array<string, mixed> $article */
    $article = $article ?? [];
    $isLast = (bool) ($isLast ?? false);
    $readMoreLabel = $readMoreLabel ?? front_trans('articles.read_more');
    $themeRootClass = $themeRootClass ?? '';
@endphp
<div class="news-item imas-article-card imas-card imas-card--article w-100 h-100 {{ $isLast ? 'no-mb' : '' }} {{ $themeRootClass }}">
    <a href="{{ $article['url'] ?? '#' }}" class="news-img-link imas-article-card__img-link imas-card__media">
        <div class="news-item-img imas-article-card__img imas-card__img">
            <img
                class="img-responsive"
                src="{{ $article['image'] ?? asset('images/blank.png') }}"
                alt="{{ $article['image_alt'] ?? ($article['title'] ?? '') }}"
                @if (! empty($article['image_title'])) title="{{ $article['image_title'] }}" @endif
            >
        </div>
    </a>
    <div class="news-item-text imas-card__body">
        <a href="{{ $article['url'] ?? '#' }}">
            <h3 class="imas-card__title">{{ $article['title'] ?? '' }}</h3>
        </a>
        <div class="dates">
            @if (! empty($article['date']))
                <span class="date">{{ $article['date'] }} &nbsp;/</span>
            @endif
            <ul class="action-list pl-0">
                <li class="action-item pl-2">
                    <i class="fa fa-eye"></i>
                    <span>{{ $article['visits'] ?? 0 }}</span>
                </li>
            </ul>
        </div>
        <div class="news-item-descr big-news imas-card__excerpt text-card-excerpt">
            <p>{{ $article['excerpt'] ?? '' }}</p>
        </div>
        <div class="news-item-bottom">
            <a href="{{ $article['url'] ?? '#' }}" class="news-link imas-card__cta">{{ $readMoreLabel }}</a>
        </div>
    </div>
</div>
