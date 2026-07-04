@props(['url'])
@php($imasLogo = \Modules\Base\Support\MailBranding::logoUrl())
@php($imasName = \Modules\Base\Support\MailBranding::name())
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if ($imasLogo)
<img src="{{ $imasLogo }}" class="logo" alt="{{ $imasName }}">
@else
<span class="header-name">{{ $imasName }}</span>
@endif
</a>
</td>
</tr>
