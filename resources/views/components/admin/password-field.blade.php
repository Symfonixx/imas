@props([
    'label' => null,
    'name' => 'password',
    'inputId' => 'password',
    'required' => false,
    'autocomplete' => 'new-password',
])

@php
    $displayLabel = $label ?? __('Password');
@endphp

<label for="{{ $inputId }}" class="form-label {{ $required ? 'required' : '' }}">{{ $displayLabel }}</label>
<div class="position-relative">
    <input type="password"
           id="{{ $inputId }}"
           class="form-control form-control-solid pe-12 @error($name) is-invalid @enderror"
           name="{{ $name }}"
           autocomplete="{{ $autocomplete }}"
           @if($required) required @endif
        {{ $attributes }}>
    @error($name)
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
    @enderror
    <button type="button"
            class="btn btn-icon btn-sm btn-active-light-primary position-absolute top-50 end-0 translate-middle-y me-1"
            data-password-toggle
            data-password-target="#{{ $inputId }}"
            aria-label="{{ __('Show password') }}"
            title="{{ __('Show password') }}">
        <i class="fa-solid fa-eye" data-password-icon-show></i>
        <i class="fa-solid fa-eye-slash d-none" data-password-icon-hide></i>
    </button>
</div>

@once
    @push('scripts')
        <script>
            (function () {
                var labels = {
                    show: @json(__('Show password')),
                    hide: @json(__('Hide password'))
                };
                document.addEventListener('click', function (e) {
                    var btn = e.target.closest('[data-password-toggle]');
                    if (!btn) {
                        return;
                    }
                    var sel = btn.getAttribute('data-password-target');
                    if (!sel) {
                        return;
                    }
                    var input = document.querySelector(sel);
                    if (!input) {
                        return;
                    }
                    var showIcon = btn.querySelector('[data-password-icon-show]');
                    var hideIcon = btn.querySelector('[data-password-icon-hide]');
                    var showPlain = input.type === 'password';
                    input.type = showPlain ? 'text' : 'password';
                    if (showIcon) {
                        showIcon.classList.toggle('d-none', showPlain);
                    }
                    if (hideIcon) {
                        hideIcon.classList.toggle('d-none', !showPlain);
                    }
                    btn.setAttribute('aria-label', showPlain ? labels.hide : labels.show);
                    btn.setAttribute('title', showPlain ? labels.hide : labels.show);
                });
            })();
        </script>
    @endpush
@endonce
