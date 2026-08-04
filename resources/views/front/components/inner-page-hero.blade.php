@php
    $pageTitle = $pageTitle ?? '';
    $items = $items ?? [];
    $bannerImageUrl = trim((string) ($bannerImageUrl ?? ''));
    $bannerVideoEmbed = trim((string) ($bannerVideoEmbed ?? ''));
    $capitalizeTitle = (bool) ($capitalizeTitle ?? true);
    $heroVideoSrc = youtube_hero_embed_src($bannerVideoEmbed);
    $hasVideo = $heroVideoSrc !== '';
    $locale = $locale ?? app()->getLocale();
    $isRtl = ($text_direction ?? '') === 'rtl' || $locale === 'ar';
    $usesConnected = $isRtl || preg_match('/[\x{0600}-\x{06FF}]/u', $pageTitle);
    $displayTitle = $pageTitle;
    if (! $usesConnected && $capitalizeTitle) {
        $displayTitle = mb_convert_case(mb_strtolower($pageTitle, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
    }
    $bgStyle = '';
    if (! $hasVideo && $bannerImageUrl !== '' && ! preg_match('#/default\.jpg(?:\?.*)?$#i', $bannerImageUrl)) {
        $escaped = str_replace('"', '\\"', $bannerImageUrl);
        $bgStyle = 'background-image: linear-gradient(color-mix(in srgb, var(--brand-navy-hover) 72%, transparent), color-mix(in srgb, var(--bg) 88%, transparent)), url("'.$escaped.'"); background-size: cover; background-position: center;';
    }
@endphp
<header class="imas-inner-page-heading-hero {{ $hasVideo ? 'imas-inner-page-heading-hero--video' : '' }}">
    <div
        class="imas-inner-page-heading-hero__bg {{ $hasVideo ? 'imas-inner-page-heading-hero__bg--video' : '' }}"
        @if ($bgStyle !== '') style="{{ $bgStyle }}" @endif
    >
        @if ($hasVideo)
            <iframe
                class="imas-inner-page-heading-hero__video"
                src="{{ $heroVideoSrc }}"
                title=""
                tabindex="-1"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                aria-hidden="true"
            ></iframe>
        @endif
    </div>
    <div class="imas-inner-page-heading-hero__inner">
        <h1
            class="imas-inner-page-heading-hero__title {{ $usesConnected ? 'imas-inner-page-heading-hero__title--connected' : '' }}"
            aria-label="{{ $pageTitle }}"
        >
            <span class="imas-inner-page-heading-hero__title-text">{{ $displayTitle }}</span>
        </h1>
        @if (count($items))
            <nav class="imas-inner-page-heading-hero__crumbs" aria-label="Breadcrumb">
                @foreach ($items as $idx => $item)
                    @if (! empty($item['href']))
                        <a href="{{ $item['href'] }}" class="imas-inner-page-heading-hero__crumb-link">{{ $item['title'] }}</a>
                    @else
                        <span class="imas-inner-page-heading-hero__crumb-active">{{ $item['title'] }}</span>
                    @endif
                    @if ($idx < count($items) - 1)
                        <span class="imas-inner-page-heading-hero__crumb-sep" aria-hidden="true">/</span>
                    @endif
                @endforeach
            </nav>
        @endif
    </div>
</header>
