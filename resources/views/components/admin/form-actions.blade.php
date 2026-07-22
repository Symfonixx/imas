@props([
    'discardUrl' => null,
    'submitLabel' => 'Save Changes',
    'discardLabel' => 'Discard',
    'hint' => 'Review your changes before saving.',
])

<div {{ $attributes->class(['imas-admin-form-actions']) }}>
    @if($slot->isNotEmpty())
        {{ $slot }}
    @else
        <div class="imas-admin-form-actions__meta">
            <span class="imas-admin-form-actions__dot" aria-hidden="true"></span>
            <span class="imas-admin-form-actions__hint">{{ __($hint) }}</span>
        </div>
        <div class="imas-admin-form-actions__btns">
            <a href="{{ $discardUrl ?? url()->previous() }}"
               class="btn btn-light btn-active-light-primary imas-admin-form-actions__discard">
                <i class="bi bi-x-lg" aria-hidden="true"></i>
                <span>{{ __($discardLabel) }}</span>
            </a>
            <button type="submit" class="btn btn-primary imas-admin-form-actions__save">
                <i class="bi bi-check2-circle" aria-hidden="true"></i>
                <span class="indicator-label">{{ __($submitLabel) }}</span>
            </button>
        </div>
    @endif
</div>
