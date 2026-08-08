{{-- Non-blocking stylesheet: preload → apply on load (PageSpeed render-blocking fix). --}}
@props([
    'href',
    'id' => null,
])
<link rel="preload" href="{{ $href }}" as="style" @if ($id) id="{{ $id }}-preload" @endif onload="this.onload=null;this.rel='stylesheet'@if ($id);this.id='{{ $id }}'@endif">
<noscript><link rel="stylesheet" href="{{ $href }}" @if ($id) id="{{ $id }}" @endif></noscript>
