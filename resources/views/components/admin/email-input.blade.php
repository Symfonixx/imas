@props([
    'inputId',
    'name' => 'email',
    'value' => '',
    'label' => null,
    'required' => false,
])

@php
    $displayLabel = $label ?? __('Email');
    $storedValue = old($name, $value);
@endphp

<label for="{{ $inputId }}" class="form-label {{ $required ? 'required' : '' }}">{{ $displayLabel }}</label>
<input
    type="email"
    id="{{ $inputId }}"
    class="form-control form-control-solid @error($name) is-invalid @enderror"
    name="{{ $name }}"
    value="{{ $storedValue }}"
    maxlength="255"
    autocomplete="email"
    inputmode="email"
    spellcheck="false"
    data-admin-email-input
    @if($required) required @endif
    {{ $attributes }}
>
@error($name)
    <span class="invalid-feedback d-block" role="alert">
        <strong>{{ $message }}</strong>
    </span>
@enderror
<span class="invalid-feedback d-block" role="alert" data-admin-email-feedback hidden>
    <strong data-admin-email-feedback-text></strong>
</span>

@once
    @push('scripts')
        <script>
            (function () {
                var EMAIL_PATTERN = /^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/;
                var messages = {
                    required: @json(__('Please enter an email address.')),
                    invalid: @json(__('Please enter a valid email address.')),
                    max: @json(__('Email may not be greater than 255 characters.'))
                };

                function normalizeEmail(value) {
                    return String(value ?? '').trim().toLowerCase();
                }

                function validateEmailInput(input) {
                    var value = normalizeEmail(input.value);
                    var feedback = input.parentElement?.querySelector('[data-admin-email-feedback]');
                    var feedbackText = feedback?.querySelector('[data-admin-email-feedback-text]');
                    var isRequired = input.hasAttribute('required');
                    var message = '';

                    if (!value) {
                        message = isRequired ? messages.required : '';
                    } else if (value.length > 255) {
                        message = messages.max;
                    } else if (!EMAIL_PATTERN.test(value)) {
                        message = messages.invalid;
                    }

                    input.setCustomValidity(message);
                    input.classList.toggle('is-invalid', Boolean(message));

                    if (feedback && feedbackText) {
                        feedbackText.textContent = message;
                        feedback.hidden = !message;
                    }

                    return !message;
                }

                function initEmailInput(input) {
                    if (!input || input.dataset.adminEmailReady === '1') {
                        return;
                    }
                    input.dataset.adminEmailReady = '1';

                    input.addEventListener('blur', function () {
                        var normalized = normalizeEmail(input.value);
                        if (normalized !== input.value) {
                            input.value = normalized;
                        }
                        validateEmailInput(input);
                    });

                    input.addEventListener('input', function () {
                        if (input.classList.contains('is-invalid')) {
                            validateEmailInput(input);
                        }
                    });

                    var form = input.closest('form');
                    if (form && form.dataset.adminEmailSubmitBound !== '1') {
                        form.dataset.adminEmailSubmitBound = '1';
                        form.addEventListener('submit', function (event) {
                            var inputs = form.querySelectorAll('[data-admin-email-input]');
                            var valid = true;

                            inputs.forEach(function (field) {
                                var normalized = normalizeEmail(field.value);
                                if (normalized !== field.value) {
                                    field.value = normalized;
                                }
                                if (!validateEmailInput(field)) {
                                    valid = false;
                                }
                            });

                            if (!valid) {
                                event.preventDefault();
                                event.stopPropagation();
                                var firstInvalid = form.querySelector('[data-admin-email-input].is-invalid');
                                if (firstInvalid) {
                                    firstInvalid.focus();
                                }
                            }
                        });
                    }
                }

                function initAllEmailInputs() {
                    document.querySelectorAll('[data-admin-email-input]').forEach(initEmailInput);
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initAllEmailInputs);
                } else {
                    initAllEmailInputs();
                }
            })();
        </script>
    @endpush
@endonce
