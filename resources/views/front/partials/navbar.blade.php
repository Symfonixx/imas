@php
    $nav_links = $nav_links ?? [];
    $locale_switcher = $locale_switcher ?? [];
    $auth = $auth ?? null;
    $globals = $globals ?? [];
    $settings = $settings ?? [];
    $appName = $appName ?? config('app.name');
    $locale = $locale ?? app()->getLocale();
    $isRtl = ($text_direction ?? '') === 'rtl' || $locale === 'ar';
    $transparentNavbar = (bool) ($navbar_transparent ?? false);
    $media = $globals['media'] ?? [];
    $logoUrl = $media['transparent_logo'] ?? ($media['white_logo'] ?? '');
    $websiteName = strtoupper(trim((string) (($globals['seo']['website_name'] ?? null) ?: $appName)));
    $websiteSlogan = 'MOST ACCURATE SOLUTIONS';
    $flagMap = ['en' => 'gb', 'tr' => 'tr', 'ar' => 'sa'];
    $currentPath = '/'.trim(request()->path(), '/');
    if ($currentPath === '/') {
        $currentPath = '/';
    }
    $normalizePath = static function (?string $url): string {
        if (! is_string($url) || trim($url) === '') {
            return '';
        }
        $path = parse_url($url, PHP_URL_PATH) ?: $url;
        $path = '/'.trim($path, '/');

        return $path === '/' ? '/' : rtrim($path, '/');
    };
    $isActive = static function (array $item) use ($normalizePath, $currentPath): bool {
        $href = $item['href'] ?? null;
        if (! is_string($href) || $href === '' || $href === '#') {
            return false;
        }
        $target = $normalizePath($href);
        if ($target === '' || $target === '#') {
            return false;
        }
        if ($currentPath === $target) {
            return true;
        }
        if (($item['key'] ?? '') === 'navBar.Home') {
            try {
                return $currentPath === $normalizePath(route('home'));
            } catch (\Throwable) {
                return false;
            }
        }
        if ($target !== '/' && str_starts_with($currentPath, $target.'/')) {
            return true;
        }

        return false;
    };
    $favoritesHref = route('property.favorites');
    $favoritesActive = $normalizePath($favoritesHref) === $currentPath;
    $accountGreeting = front_trans('Hi');
    if ($auth && ! empty($auth['nav_display_name'])) {
        $accountGreeting .= ' '.$auth['nav_display_name'];
    }
    $isAdmin = ($auth['type'] ?? null) === 'admin';
@endphp
<header
    id="header-container"
    class="header imas-nav-shell {{ $transparentNavbar ? 'head-tr' : 'imas-navbar-solid' }}"
>
    <div id="header" class="imas-nav imas-nav__bar bottom">
        <div class="container imas-nav__container">
            <div id="logo" class="imas-nav__logo">
                <a href="{{ route('home') }}" class="imas-nav__logo-link">
                    @if ($logoUrl)
                        <img src="{{ $logoUrl }}" data-sticky-logo="{{ $logoUrl }}" alt="">
                    @endif
                    <span class="imas-brand-text">
                        <span class="website-name">{{ $websiteName }}</span>
                        <span class="website-slogan">{{ $websiteSlogan }}</span>
                    </span>
                </a>
            </div>

            <nav id="navigation" class="imas-nav__menu style-1">
                <ul id="responsive">
                    @foreach ($nav_links as $item)
                        <li class="imas-nav-item {{ ! empty($item['children']) ? 'has-submenu' : '' }}">
                            @if (! empty($item['href']))
                                <a href="{{ $item['href'] }}" class="{{ $isActive($item) ? 'active' : '' }}">
                                    {{ $item['label'] ?? front_trans($item['key'] ?? '') }}
                                </a>
                            @else
                                <a href="#" class="{{ $isActive($item) ? 'active' : '' }}">
                                    {{ $item['label'] ?? front_trans($item['key'] ?? '') }}
                                </a>
                            @endif
                            @if (! empty($item['children']))
                                <ul class="imas-nav__submenu">
                                    @foreach ($item['children'] as $child)
                                        <li class="imas-nav__submenu-item">
                                            <a
                                                href="{{ $child['href'] }}"
                                                class="imas-nav__submenu-link {{ $isActive($child) ? 'active' : '' }}"
                                            >
                                                {{ $child['label'] ?? front_trans($child['key'] ?? '') }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </li>
                    @endforeach
                    @guest
                        <li class="imas-mmenu-only">
                            <a href="#" class="imas-auth-nav-link" data-open-auth="login">{{ front_trans('Login') }}</a>
                        </li>
                        <li class="imas-mmenu-only">
                            <a href="#" class="imas-auth-nav-link" data-open-auth="register">{{ front_trans('Register') }}</a>
                        </li>
                    @endguest
                </ul>
            </nav>

            <div class="imas-nav__end">
                <div class="imas-nav__actions right {{ $isRtl ? 'imas-nav__actions--rtl' : '' }}">
                    <div class="header-user-menu user-menu add imas-nav__lang imas-header-action">
                        <div class="lang-wrap" data-dropdown>
                            <div
                                class="show-lang imas-nav__lang-trigger"
                                role="button"
                                tabindex="0"
                                data-dropdown-toggle
                                aria-expanded="false"
                                aria-haspopup="listbox"
                                aria-label="{{ front_trans('Language') }}"
                            >
                                <span class="show-lang-trigger-inner">
                                    @if (! empty($flagMap[$locale]))
                                        <span class="fi lang-switch-flag lang-switch-flag--trigger fi-{{ $flagMap[$locale] }}" aria-hidden="true"></span>
                                    @endif
                                </span>
                                <i class="fa fa-caret-down arrlan"></i>
                            </div>
                            <ul class="lang-tooltip lang-action no-list-style" role="listbox">
                                @foreach ($locale_switcher as $loc)
                                    <li>
                                        <a
                                            href="{{ $loc['url'] }}"
                                            class="lang-switch-row {{ ($loc['code'] ?? '') === $locale ? 'current-lan' : '' }}"
                                            role="option"
                                            aria-selected="{{ ($loc['code'] ?? '') === $locale ? 'true' : 'false' }}"
                                        >
                                            @if (! empty($flagMap[$loc['code'] ?? '']))
                                                <span class="fi lang-switch-flag fi-{{ $flagMap[$loc['code']] }}" aria-hidden="true"></span>
                                            @endif
                                            <span class="mx-2">{{ $loc['native'] ?? $loc['code'] }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    @auth
                        <a
                            href="{{ $favoritesHref }}"
                            class="imas-nav__favorites imas-header-action {{ $favoritesActive ? 'is-active' : '' }}"
                            aria-label="{{ front_trans('properties.favorite_properties') }}"
                            title="{{ front_trans('properties.favorite_properties') }}"
                        >
                            <i class="fa fa-heart" aria-hidden="true"></i>
                        </a>
                        <div class="header-user-menu user-menu add UserMenu imas-header-action" data-dropdown>
                            <div
                                class="header-user-name imas-nav__account-trigger {{ $isRtl ? 'imas-nav__account-trigger--rtl' : '' }}"
                                role="button"
                                tabindex="0"
                                data-dropdown-toggle
                                aria-expanded="false"
                                aria-haspopup="true"
                                aria-label="{{ front_trans('Account menu') }}"
                            >
                                <span class="imas-nav__avatar">
                                    <img src="{{ $auth['avatar'] ?? $auth['img'] ?? asset('images/blank.png') }}" alt="">
                                </span>
                                <span class="imas-nav__account-text imas-nav__desktop-only">{{ $accountGreeting }}</span>
                                <i class="fa fa-caret-down imas-nav__account-caret imas-nav__desktop-only" aria-hidden="true"></i>
                            </div>
                            <ul class="imas-user-menu-dropdown text-start">
                                @if ($isAdmin)
                                    <li>
                                        <a class="imas-user-menu-dropdown__item" href="{{ route('admin.dashboard.index') }}">
                                            {{ front_trans('Dashboard') }}
                                        </a>
                                    </li>
                                    <li>
                                        <a class="imas-user-menu-dropdown__item" href="{{ route('admin.profile.edit') }}">
                                            {{ front_trans('global.profile') }}
                                        </a>
                                    </li>
                                @endif
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="imas-user-menu-dropdown__item dropdown-logout">
                                            {{ front_trans('global.LogOut') }}
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @else
                        <div class="imas-nav__sign-in imas-header-action">
                            <a href="#" class="imas-nav__sign-in-link show-reg-form modal-open" data-open-auth="login">
                                {{ front_trans('Sign In') }}
                            </a>
                        </div>
                    @endauth
                </div>

                <div class="mmenu-trigger imas-nav__mmenu">
                    <button class="hamburger hamburger--collapse" type="button" aria-label="{{ front_trans('Menu') }}">
                        <span class="hamburger-box">
                            <span class="hamburger-inner"></span>
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</header>
