@php
    /** @var array<string, mixed> $property */
    $property = $property ?? [];
    $columnClass = $columnClass ?? 'col-lg-4 col-md-6 col-xs-12';
    $locale = $locale ?? app()->getLocale();
    $isSoldOut = (bool) ($property['is_sold_out'] ?? false);
    $isFavorited = (bool) ($property['is_favorited'] ?? false);
    $typeName = front_localized($property['property_type']['name'] ?? null, $locale);
    $displayTitle = trim((string) ($property['title'] ?? ''));
    if ($displayTitle === '') {
        $displayTitle = (string) ($property['project_name'] ?? $property['project_code'] ?? 'Property');
    }
    $showUrl = trim((string) ($property['url'] ?? ''));
    if ($showUrl === '') {
        $slug = $property['url_key'] ?? $property['slug'] ?? $property['project_code'] ?? null;
        $showUrl = $slug ? route('property.show', $slug) : '#';
    }
    $addressLine = property_location_line($property['location'] ?? null, $locale) ?: '—';
    $overviewText = front_strip_html(front_localized($property['overview'] ?? null, $locale));
    $priceAmount = format_property_money(property_start_price($property), $locale);
    $unitTypes = $property['unit_types'] ?? [];
    $youtube = trim((string) ($property['youtube_video_url'] ?? ''));
    $authUser = $auth ?? null;
@endphp
<div
    class="imas-card imas-card--property imas-card--media-overlay imas-property-card imas-property-card--media-overlay item user-select-none {{ $columnClass }} {{ $isSoldOut ? 'imas-property-card--sold-out' : '' }}"
    @if ($isSoldOut) aria-disabled="true" @endif
    x-data="{
        favorited: {{ $isFavorited ? 'true' : 'false' }},
        videoOpen: false,
        async toggleFavorite(e) {
            e.preventDefault();
            e.stopPropagation();
            if ({{ $isSoldOut ? 'true' : 'false' }}) return;
            @guest
                window.dispatchEvent(new CustomEvent('imas-open-auth', { detail: { tab: 'login' } }));
                return;
            @endguest
            const next = !this.favorited;
            const prev = this.favorited;
            this.favorited = next;
            try {
                if (next) {
                    await window.axios.post('/api/favorites', { property_id: {{ (int) ($property['id'] ?? 0) }} });
                } else {
                    await window.axios.delete('/api/favorites/{{ (int) ($property['id'] ?? 0) }}');
                }
            } catch (err) {
                this.favorited = prev;
            }
        }
    }"
>
    <div class="project-single imas-card__surface">
        @unless ($isSoldOut)
            <a href="{{ $showUrl }}" class="imas-property-card__stretched-link" aria-label="{{ $displayTitle }}"></a>
        @endunless
        <div class="project-inner project-head imas-card__media">
            <div class="homes">
                <div class="homes-img" @if ($isSoldOut) aria-label="{{ $displayTitle }} – {{ front_trans('properties.sold_out') }}" @endif>
                    @if ($typeName !== '' || ! empty($property['is_featured']))
                        <div class="homes-tag button alt imas-badge--type">
                            @if (! empty($property['is_featured']))
                                <i class="fa fa-star imas-featured-star" aria-label="{{ front_trans('properties.featured') }}"></i>
                            @endif
                            @if ($typeName !== '')
                                <span>{{ $typeName }}</span>
                            @endif
                        </div>
                    @endif
                    @if (! empty($property['is_sold_out']))
                        <div class="homes-tag button alt imas-sold-out-badge imas-badge--danger">
                            {{ front_trans('properties.sold_out') }}
                        </div>
                    @endif
                    <img
                        src="{{ $property['thumbnail_url'] ?? asset('images/blank.png') }}"
                        alt="{{ $property['thumbnail_alt'] ?? $displayTitle }}"
                        @if (! empty($property['thumbnail_title'])) title="{{ $property['thumbnail_title'] }}" @endif
                        class="img-responsive"
                    >
                </div>
            </div>
            <div class="imas-card-actions">
                <div class="homes-price imas-start-price imas-chip">
                    <span class="imas-start-price__from">{{ front_trans('properties.price_from') }}</span>
                    <span class="imas-start-price__amount">{{ $priceAmount }}</span>
                </div>
                @unless ($isSoldOut)
                    <div class="button-effect">
                        @if ($youtube !== '')
                            <a
                                href="{{ $youtube }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="btn imas-card-video-btn"
                                aria-label="{{ front_trans('property_show.play_video') }}"
                                onclick="event.stopPropagation()"
                            >
                                <i class="fas fa-video" aria-hidden="true"></i>
                            </a>
                        @endif
                        <button
                            type="button"
                            class="btn imas-favorite-btn"
                            :class="{ 'is-favorited': favorited }"
                            :aria-pressed="favorited"
                            aria-label="{{ front_trans('properties.add_favorite') }}"
                            @click="toggleFavorite"
                        >
                            <i class="fa favorite-icon" :class="favorited ? 'fa-heart' : 'fa-heart-o'" aria-hidden="true"></i>
                        </button>
                    </div>
                @endunless
            </div>
        </div>
        <div class="homes-content imas-card__body">
            <h3 class="imas-property-title imas-card__title">
                <span class="imas-card__title-text">{{ $displayTitle }}</span>
            </h3>
            @if ($overviewText !== '')
                <p class="imas-property-overview imas-card__excerpt text-card-excerpt mb-3">{{ $overviewText }}</p>
            @endif
            <p class="homes-address imas-card__meta text-base mb-3">
                <span class="imas-card__address-line">
                    <i class="fa fa-map-marker imas-address-marker" aria-hidden="true"></i>
                    <span>{{ $addressLine }}</span>
                </span>
            </p>
            @if (count($unitTypes) > 0)
                @php
                    $firstUnit = $unitTypes[0];
                    $unitName = front_localized($firstUnit['name'] ?? '', $locale);
                    $minA = $firstUnit['min_area'] ?? null;
                    $maxA = $firstUnit['max_area'] ?? null;
                    $areaLabel = '';
                    if (is_numeric($minA) && is_numeric($maxA) && (float) $minA !== (float) $maxA) {
                        $areaLabel = $minA.'–'.$maxA.' m²';
                    } elseif (is_numeric($minA)) {
                        $areaLabel = $minA.' m²';
                    } elseif (is_numeric($maxA)) {
                        $areaLabel = $maxA.' m²';
                    }
                    $count = count($unitTypes);
                    $countLabel = $count === 1
                        ? front_trans('properties.unit_types_count_one')
                        : str_replace(':count', (string) $count, front_trans('properties.unit_types_count'));
                @endphp
                <div class="imas-unit-types-bar text-base pb-3" aria-label="{{ front_trans('properties.unit_types_aria') }}">
                    <div class="imas-unit-types-bar__left">
                        <i class="fa fa-building imas-unit-types-bar__icon" aria-hidden="true"></i>
                        <div class="imas-unit-types-flip">
                            <div class="imas-unit-types-flip__slide">
                                <span class="imas-unit-types-flip__name">{{ $unitName }}</span>
                                @if ($areaLabel !== '')
                                    <span class="imas-unit-types-flip__sep" aria-hidden="true">→</span>
                                    <span class="imas-unit-types-flip__area" dir="ltr">{{ $areaLabel }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <span class="imas-unit-types-bar__count">
                        <i class="fa fa-circle imas-unit-types-bar__dot" aria-hidden="true"></i>
                        {{ $countLabel }}
                    </span>
                </div>
            @endif
        </div>
    </div>
</div>
