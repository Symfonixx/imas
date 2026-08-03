@php
    $settings = $settings ?? [];
    $globals = $globals ?? [];
    $nav_links = $nav_links ?? [];
    $appName = $appName ?? config('app.name');
    $media = $globals['media'] ?? [];
    $logoUrl = $media['transparent_logo'] ?? ($media['white_logo'] ?? '');
    $websiteName = strtoupper(trim((string) ($globals['seo']['website_name'] ?? '')));
    $websiteSlogan = 'MOST ACCURATE SOLUTIONS';
    $fallbackAddress = '95 South Park Avenue, USA';
    $fallbackPhone = '+456 875 369 208';
    $fallbackEmail = 'support@example.com';
    $rawPhone = trim((string) ($settings['contact_phone'] ?? $settings['phone'] ?? ''));
    $phoneDisplay = $rawPhone !== ''
        ? (format_turkish_phone($rawPhone) ?: $rawPhone)
        : (format_turkish_phone($fallbackPhone) ?: $fallbackPhone);
    $contactPhone = $rawPhone
        ?: trim((string) ($globals['contact']['phone'] ?? ''))
        ?: $fallbackPhone;
    $normalized = normalize_turkish_phone_digits($contactPhone);
    $phoneHref = whatsapp_contact_href(
        $globals['social']['whatsapp'] ?? ($settings['whatsapp'] ?? null),
        $normalized !== '' ? '+'.$normalized : $contactPhone,
    );
    $mainNavLinks = array_values(array_filter($nav_links, static fn ($l) => ! empty($l['href'])));
    $footerPages = $globals['pages']['footer'] ?? [];
    $bottomBarPages = $globals['pages']['bottom_bar'] ?? [];
    $socialDefs = [
        ['key' => 'facebook', 'label' => 'Facebook', 'icon' => 'fa fa-facebook'],
        ['key' => 'twitter', 'label' => 'Twitter', 'icon' => 'fa fa-twitter'],
        ['key' => 'instagram', 'label' => 'Instagram', 'icon' => 'fab fa-instagram'],
        ['key' => 'youtube', 'label' => 'YouTube', 'icon' => 'fa fa-youtube'],
        ['key' => 'tiktok', 'label' => 'TikTok', 'icon' => 'fab fa-tiktok'],
    ];
    $footerSocialLinks = [];
    foreach ($socialDefs as $def) {
        $href = trim((string) ($settings[$def['key']] ?? ''));
        if ($href !== '') {
            $footerSocialLinks[] = array_merge($def, ['href' => $href]);
        }
    }
    $subscribeUrl = $subscribe_store_url ?? route('support.newsletter.subscribe');
@endphp
<footer class="first-footer rec-pro imas-blog-footer">
    <div class="top-footer">
        <div class="container imas-footer-wrap">
            <div class="row imas-footer-grid">
                <div class="col-lg-3 col-md-6 f-col imas-footer-col--brand">
                    <div class="netabout">
                        <div class="brand-line">
                            <div class="logo">
                                @if ($logoUrl)
                                    <img src="{{ $logoUrl }}" alt="logo" class="footer_logo">
                                @endif
                            </div>
                            <div class="imas-brand-text">
                                <span class="website-name">{{ $websiteName }}</span>
                                <span class="website-slogan">{{ $websiteSlogan }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="contactus text-start">
                        <ul>
                            <li class="contact-line">
                                <div class="info">
                                    <span class="ic" aria-hidden="true"><i class="fa fa-map-marker"></i></span>
                                    <p class="in-p">{{ $settings['contact_address'] ?? $fallbackAddress }}</p>
                                </div>
                            </li>
                            <li class="contact-line">
                                <div class="info">
                                    <span class="ic" aria-hidden="true"><i class="fa fa-phone"></i></span>
                                    <p class="in-p in-p--phone" dir="ltr">
                                        @if ($phoneDisplay && $phoneHref)
                                            <span class="in-p-link-wrap">
                                                <a href="{{ $phoneHref }}" target="_blank" rel="noopener noreferrer">{{ $phoneDisplay }}</a>
                                            </span>
                                        @elseif ($phoneDisplay)
                                            {{ $phoneDisplay }}
                                        @endif
                                    </p>
                                </div>
                            </li>
                            <li class="contact-line">
                                <div class="info">
                                    <span class="ic" aria-hidden="true"><i class="fa fa-envelope"></i></span>
                                    <p class="in-p ti">{{ $settings['contact_email'] ?? $fallbackEmail }}</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 f-col">
                    <div class="navigation text-start">
                        <h3>{{ front_trans('navBar.navigation') }}</h3>
                        <div class="nav-footer text-start">
                            <ul class="links">
                                @foreach ($mainNavLinks as $item)
                                    <li>
                                        <a href="{{ $item['href'] }}">{{ front_trans($item['key'] ?? '') }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 f-col">
                    <div class="navigation text-start">
                        <h3>{{ front_trans('navBar.useful_links') }}</h3>
                        <ul class="links links--single">
                            @foreach ($footerPages as $p)
                                <li>
                                    <a href="{{ route('page.show', ['slug' => $p['slug']]) }}">{{ $p['title'] }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 f-col">
                    <div class="newsletters text-start">
                        <h3>{{ front_trans('navBar.newsLetters') }}</h3>
                        <p>{{ front_trans('navBar.signup_for_newsletters') }}</p>
                    </div>
                    <form
                        class="bloq-email mailchimp form-inline newsletter"
                        method="POST"
                        action="{{ $subscribeUrl }}"
                        x-data="{ success: false }"
                        @submit="success = false"
                    >
                        @csrf
                        <div class="email">
                            <input
                                id="subscribeEmail"
                                type="email"
                                name="email"
                                required
                                maxlength="255"
                                value="{{ old('email') }}"
                                placeholder="{{ front_trans('navBar.enter_your_email') }}"
                                class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
                            >
                            <button type="submit">{{ front_trans('navBar.subscribe') }}</button>
                        </div>
                        @error('email')
                            <p class="subscription-error" role="alert">{{ $message }}</p>
                        @enderror
                        @if (session('status') === 'subscribed' || session('newsletter_success'))
                            <p class="subscription-success" role="status">{{ front_trans('navBar.subscription_success') }}</p>
                        @endif
                    </form>
                    @if (count($footerSocialLinks))
                        <div class="socials imas-footer-socials" aria-label="{{ front_trans('Social media') }}">
                            @foreach ($footerSocialLinks as $item)
                                <a href="{{ $item['href'] }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $item['label'] }}">
                                    <i class="{{ $item['icon'] }}" aria-hidden="true"></i>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="second-footer rec-pro copyright">
        <div class="container imas-footer-wrap imas-second-footer__inner">
            @if (count($bottomBarPages))
                <nav class="imas-second-footer__bottom-bar" aria-label="{{ front_trans('navBar.useful_links') }}">
                    @foreach ($bottomBarPages as $index => $p)
                        @if ($index > 0)
                            <span class="imas-second-footer__separator" aria-hidden="true">|</span>
                        @endif
                        <a class="imas-second-footer__page-link" href="{{ route('page.show', ['slug' => $p['slug']]) }}">
                            {{ $p['title'] }}
                        </a>
                    @endforeach
                </nav>
            @else
                <div class="imas-second-footer__bottom-bar imas-second-footer__bottom-bar--empty" aria-hidden="true"></div>
            @endif
            <p class="imas-second-footer__copy">
                {{ date('Y') }} © {{ $appName }} — {{ front_trans('navBar.All Rights Reserved') }}
            </p>
            <p class="imas-second-footer__developer">
                <span>{{ front_trans('Developed By Symfonix') }}</span>
                <a href="https://symfonix.io/" target="_blank" rel="noopener noreferrer" class="imas-second-footer__developer-link">
                    {{ front_trans('Go to website') }}
                </a>
            </p>
        </div>
    </div>
</footer>

<a data-scroll href="#wrapper" class="go-up">
    <i class="fa fa-angle-double-up" aria-hidden="true"></i>
</a>
