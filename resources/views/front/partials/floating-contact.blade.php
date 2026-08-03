@php
    $globals = $globals ?? [];
    $settings = $settings ?? [];
    $contact = $globals['contact'] ?? [];
    $social = $globals['social'] ?? [];
    $contactPhone = trim((string) ($contact['phone'] ?? $settings['contact_phone'] ?? $settings['phone'] ?? ''));
    $contactEmail = trim((string) ($contact['email'] ?? $settings['contact_email'] ?? $settings['email'] ?? ''));
    $whatsappHref = whatsapp_contact_href($social['whatsapp'] ?? null, $contactPhone);
    $gmailHref = gmail_compose_url($contactEmail);
    $phoneDigits = preg_replace('/[^\d+]/', '', $contactPhone) ?? '';
    $phoneHref = $phoneDigits !== '' ? 'tel:'.$phoneDigits : '';
    $hasAny = $whatsappHref !== '#' && $whatsappHref !== '' || $gmailHref !== '' || $phoneHref !== '';
    // Treat empty/# whatsapp as missing when no digits resolved
    if ($whatsappHref === '#' || $whatsappHref === '') {
        $whatsappHref = '';
    }
    $hasAny = $whatsappHref !== '' || $gmailHref !== '' || $phoneHref !== '';
@endphp
@if ($hasAny)
<div
    class="imas-floating-contact"
    x-data="{ open: false }"
    @click.outside="open = false"
    @keydown.escape.window="open = false"
    :class="{ 'imas-floating-contact--open': open }"
>
    <div
        x-show="open"
        x-cloak
        x-transition
        id="imas-floating-contact-menu"
        class="imas-floating-contact__panel"
        role="dialog"
        aria-label="{{ front_trans('floating_contact.menu_aria') }}"
    >
        <p class="imas-floating-contact__title text-md font-semibold">
            {{ front_trans('floating_contact.menu_title') }}
        </p>
        <ul class="imas-floating-contact__list">
            @if ($whatsappHref)
                <li>
                    <a href="{{ $whatsappHref }}" class="imas-floating-contact__item" target="_blank" rel="noopener noreferrer">
                        <span class="imas-floating-contact__icon imas-floating-contact__icon--whatsapp" aria-hidden="true">
                            <i class="fa fa-whatsapp"></i>
                        </span>
                        <span class="imas-floating-contact__label text-sm font-medium">
                            {{ front_trans('floating_whatsapp.aria_label') }}
                        </span>
                    </a>
                </li>
            @endif
            @if ($gmailHref)
                <li>
                    <a href="{{ $gmailHref }}" class="imas-floating-contact__item" target="_blank" rel="noopener noreferrer">
                        <span class="imas-floating-contact__icon imas-floating-contact__icon--gmail" aria-hidden="true">
                            <i class="fab fa-google"></i>
                        </span>
                        <span class="imas-floating-contact__label text-sm font-medium">
                            {{ front_trans('floating_contact.gmail') }}
                        </span>
                    </a>
                </li>
            @endif
            @if ($phoneHref)
                <li>
                    <a href="{{ $phoneHref }}" class="imas-floating-contact__item">
                        <span class="imas-floating-contact__icon imas-floating-contact__icon--phone" aria-hidden="true">
                            <i class="fa fa-phone"></i>
                        </span>
                        <span class="imas-floating-contact__label text-sm font-medium">
                            {{ front_trans('floating_contact.direct_call') }}
                        </span>
                    </a>
                </li>
            @endif
        </ul>
    </div>

    <button
        type="button"
        class="imas-floating-contact__toggle"
        :aria-expanded="open"
        :aria-controls="open ? 'imas-floating-contact-menu' : undefined"
        :aria-label="open ? '{{ front_trans('floating_contact.aria_close') }}' : '{{ front_trans('floating_contact.aria_open') }}'"
        @click="open = !open"
    >
        <i class="fa" :class="open ? 'fa-times' : 'fa-comment'" aria-hidden="true"></i>
    </button>
</div>
@endif
