@php($imasName = \Modules\Base\Support\MailBranding::name())
@php($imasUrl = \Modules\Base\Support\MailBranding::url())
@php($imasTagline = \Modules\Base\Support\MailBranding::tagline())
@php($imasEmail = \Modules\Base\Support\MailBranding::contactEmail())
@php($imasPhone = \Modules\Base\Support\MailBranding::contactPhone())
<x-mail::layout>
{{-- Header --}}
<x-slot:header>
<x-mail::header :url="$imasUrl">
{{ $imasName }}
</x-mail::header>
</x-slot:header>

{{-- Body --}}
{{ $slot }}

{{-- Subcopy --}}
@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{{ $subcopy }}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

{{-- Footer --}}
<x-slot:footer>
<x-mail::footer>
<span class="footer-brand">{{ $imasName }}</span>
@if ($imasTagline)
<br><span class="footer-tagline">{{ $imasTagline }}</span>
@endif
@if ($imasEmail || $imasPhone)
<br><span class="footer-contact">
@if ($imasPhone){{ $imasPhone }}@endif
@if ($imasEmail && $imasPhone) &nbsp;•&nbsp; @endif
@if ($imasEmail)<a href="mailto:{{ $imasEmail }}">{{ $imasEmail }}</a>@endif
</span>
@endif
<br><span class="footer-legal">© {{ date('Y') }} {{ $imasName }}. @lang('All rights reserved.')</span>
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
