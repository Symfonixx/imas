@php
    $settings = $settings ?? [];
    $globals = $globals ?? [];
    $topBarPages = $globals['pages']['top_bar'] ?? [];
    $fallbackPhone = '+456 875 369 208';
    $fallbackEmail = 'support@example.com';
    $rawPhone = trim((string) ($settings['contact_phone'] ?? $settings['phone'] ?? ''));
    $phoneDisplay = $rawPhone !== ''
        ? (format_turkish_phone($rawPhone) ?: $rawPhone)
        : (format_turkish_phone($fallbackPhone) ?: $fallbackPhone);
    $emailDisplay = trim((string) ($settings['contact_email'] ?? $settings['email'] ?? '')) ?: $fallbackEmail;
    $contactPhone = $rawPhone
        ?: trim((string) ($globals['contact']['phone'] ?? ''))
        ?: $fallbackPhone;
    $normalized = normalize_turkish_phone_digits($contactPhone);
    $phoneForWa = $normalized !== '' ? '+'.$normalized : $contactPhone;
    $phoneHref = whatsapp_contact_href(
        $globals['social']['whatsapp'] ?? ($settings['whatsapp'] ?? null),
        $phoneForWa,
    );
    $emailHref = 'mailto:'.$emailDisplay;
    $hasContactInfo = $phoneDisplay !== '' || $emailDisplay !== '';
    $socialDefs = [
        ['key' => 'facebook', 'label' => 'Facebook', 'icon' => 'fa fa-facebook'],
        ['key' => 'twitter', 'label' => 'Twitter', 'icon' => 'fa fa-twitter'],
        ['key' => 'instagram', 'label' => 'Instagram', 'icon' => 'fab fa-instagram'],
        ['key' => 'youtube', 'label' => 'YouTube', 'icon' => 'fa fa-youtube'],
        ['key' => 'tiktok', 'label' => 'TikTok', 'icon' => 'fab fa-tiktok'],
    ];
    $topSocialLinks = [];
    foreach ($socialDefs as $def) {
        $href = trim((string) ($settings[$def['key']] ?? ''));
        if ($href !== '') {
            $topSocialLinks[] = array_merge($def, ['href' => $href]);
        }
    }
@endphp
<div class="imas-top-bar topbar" role="region" aria-label="{{ front_trans('Contacts') }}">
    <div class="container imas-nav__container imas-top-bar__inner">
        <div class="imas-top-bar__contacts contact">
            @if ($phoneDisplay && $phoneHref)
                <a class="imas-top-bar__link" href="{{ $phoneHref }}" target="_blank" rel="noopener noreferrer">
                    <i class="fa fa-phone" aria-hidden="true"></i>
                    <span class="imas-top-bar__phone" dir="ltr">{{ $phoneDisplay }}</span>
                </a>
            @endif
            @if ($emailDisplay)
                <a class="imas-top-bar__link" href="{{ $emailHref }}">
                    <i class="fa fa-envelope" aria-hidden="true"></i>
                    <span>{{ $emailDisplay }}</span>
                </a>
            @endif
            @if (count($topBarPages) && $hasContactInfo)
                <span class="imas-top-bar__separator" aria-hidden="true">|</span>
            @endif
            @foreach ($topBarPages as $p)
                <a class="imas-top-bar__link imas-top-bar__page-link" href="{{ route('page.show', ['slug' => $p['slug']]) }}">
                    {{ $p['title'] }}
                </a>
            @endforeach
        </div>
        @if (count($topSocialLinks))
            <ul class="imas-top-bar__socials socials" aria-label="{{ front_trans('Social media') }}">
                @foreach ($topSocialLinks as $item)
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
