@props([
    'label' => null,
    'name' => null,
    'required' => false,
    'translatable' => false,
    'helper' => null,
    'icon' => null,
])

@php
    $hasError = $name && $errors->has($name);
    $controlId = $name ? str_replace(['[', ']'], ['_', ''], $name) : null;
@endphp

<div {{ $attributes->class([
        'fv-row imas-admin-form-group',
        'is-invalid' => $hasError,
    ]) }}>
    @if($label)
        <label class="form-label" @if($controlId) for="{{ $controlId }}" @endif>
            @if($translatable)
                <span class="imas-admin-translatable" data-bs-toggle="tooltip" title="{{ __('Translatable') }}">
                    <i class="bi bi-translate" aria-hidden="true"></i>
                </span>
            @endif
            @if($icon)
                <i class="{{ $icon }} text-primary" aria-hidden="true"></i>
            @endif
            <span>{{ __($label) }}</span>
            @if($required)
                <span class="imas-admin-required" title="{{ __('Required') }}" aria-hidden="true">*</span>
            @endif
        </label>
    @endif

    {{ $slot }}

    @if($helper)
        <div class="form-text">{!! __($helper) !!}</div>
    @endif

    @if($hasError)
        <div class="imas-admin-field-error" role="alert">{{ $errors->first($name) }}</div>
    @endif
</div>
