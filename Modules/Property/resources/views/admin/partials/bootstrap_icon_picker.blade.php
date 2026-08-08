{{--
    $name — input name (default: icon)
    $iconChoices — array<int, array{class: string, label: string}>
    $selected — full Bootstrap Icons class string e.g. bi bi-house (empty = none)
    $required — whether a selection is required (default: true)
    $compact — inline button trigger suitable for option rows (default: false)
--}}
@php
    $inputName = $name ?? 'icon';
    $pickerUid = $pickerId ?? 'pi_'.uniqid();
    $isRequired = (bool) ($required ?? true);
    $isCompact = (bool) ($compact ?? false);
    $selected = is_string($selected ?? null) ? trim((string) $selected) : '';
    $previewClass = $selected !== '' ? $selected : 'bi bi-image';
@endphp

<div class="property-icon-picker {{ $isCompact ? 'property-icon-picker--compact flex-shrink-0' : 'w-100' }}"
     id="{{ $pickerUid }}"
     data-kt-property-icon-picker
     @if(! $isRequired) data-kt-property-icon-optional="1" @endif
     @if($isCompact) data-kt-property-icon-compact="1" @endif>
    <div class="dropdown {{ $isCompact ? '' : 'w-100' }}">
        <button @class([
                    'btn d-flex align-items-center text-start',
                    'btn-icon btn-light border border-gray-300' => $isCompact,
                    'btn-light btn-active-light-primary border border-gray-300 justify-content-between gap-3 w-100 px-4 py-3' => ! $isCompact,
                ])
                type="button"
                id="{{ $pickerUid }}_toggle"
                data-bs-toggle="dropdown"
                data-bs-auto-close="outside"
                aria-expanded="false"
                aria-label="{{ __('Choose icon') }}"
                @if($isCompact) title="{{ __('Choose icon') }}" @endif>
            <span class="d-flex align-items-center gap-3 min-w-0">
                <i data-kt-property-icon-preview
                   class="{{ $previewClass }} {{ $isCompact ? 'fs-3' : 'fs-2' }} text-primary flex-shrink-0 {{ $selected === '' ? 'opacity-50' : '' }}"
                   aria-hidden="true"></i>
            </span>
            @unless($isCompact)
                <i class="bi bi-chevron-down text-gray-600 flex-shrink-0"></i>
            @endunless
        </button>
        <div class="dropdown-menu p-3 shadow-sm mt-1 {{ $isCompact ? '' : 'w-100' }}"
             aria-labelledby="{{ $pickerUid }}_toggle"
             style="{{ $isCompact ? 'min-width: 280px; max-width: min(420px, 90vw);' : '' }} max-height: min(320px, 50vh); overflow-y: auto;">
            @unless($isRequired)
                <button type="button"
                        class="btn btn-sm btn-light w-100 mb-2 border {{ $selected === '' ? 'border-primary bg-light-primary' : 'border-gray-200' }}"
                        data-kt-property-icon-value=""
                        aria-pressed="{{ $selected === '' ? 'true' : 'false' }}">
                    {{ __('No icon') }}
                </button>
            @endunless
            <div class="row g-2">
                @foreach($iconChoices as $opt)
                    @php $isActive = ($selected === $opt['class']); @endphp
                    <div class="col-4 col-sm-3 {{ $isCompact ? 'col-md-3' : 'col-md-2' }}">
                        <button type="button"
                                class="btn btn-icon btn-light w-100 py-3 rounded border {{ $isActive ? 'border-primary bg-light-primary' : 'border-gray-200' }}"
                                data-kt-property-icon-value="{{ $opt['class'] }}"
                                title="{{ $opt['label'] }}"
                                aria-label="{{ $opt['label'] }}"
                                aria-pressed="{{ $isActive ? 'true' : 'false' }}">
                            <i class="{{ $opt['class'] }} fs-3 text-primary" aria-hidden="true"></i>
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    <input type="hidden"
           name="{{ $inputName }}"
           value="{{ $selected }}"
           @if($isRequired) required @endif
           data-kt-property-icon-input
           data-field="icon">
</div>

@once
    @push('scripts')
        <script>
            (function () {
                function bindPropertyIconPicker(root) {
                    if (!root || root.getAttribute('data-kt-property-icon-bound') === '1') {
                        return;
                    }

                    var input = root.querySelector('[data-kt-property-icon-input]');
                    var preview = root.querySelector('[data-kt-property-icon-preview]');
                    if (!input || !preview) {
                        return;
                    }

                    var optional = root.getAttribute('data-kt-property-icon-optional') === '1';
                    var compact = root.getAttribute('data-kt-property-icon-compact') === '1';
                    var previewSize = compact ? 'fs-3' : 'fs-2';
                    var emptyPreview = 'bi bi-image ' + previewSize + ' text-primary flex-shrink-0 opacity-50';

                    function setActive(btn) {
                        root.querySelectorAll('[data-kt-property-icon-value]').forEach(function (b) {
                            b.classList.remove('border-primary', 'bg-light-primary');
                            b.classList.add('border-gray-200');
                            b.setAttribute('aria-pressed', 'false');
                        });
                        if (btn) {
                            btn.classList.add('border-primary', 'bg-light-primary');
                            btn.classList.remove('border-gray-200');
                            btn.setAttribute('aria-pressed', 'true');
                        }
                    }

                    root.querySelectorAll('[data-kt-property-icon-value]').forEach(function (btn) {
                        btn.addEventListener('click', function (e) {
                            e.preventDefault();
                            e.stopPropagation();
                            var icon = this.getAttribute('data-kt-property-icon-value') || '';
                            if (!optional && icon === '') {
                                return;
                            }
                            input.value = icon;
                            if (icon === '') {
                                preview.className = emptyPreview;
                            } else {
                                preview.className = icon + ' ' + previewSize + ' text-primary flex-shrink-0';
                            }
                            setActive(this);
                            var toggle = root.querySelector('[data-bs-toggle="dropdown"]');
                            if (toggle && typeof bootstrap !== 'undefined' && bootstrap.Dropdown) {
                                var dd = bootstrap.Dropdown.getInstance(toggle);
                                if (dd) {
                                    dd.hide();
                                }
                            }
                        });
                    });

                    root.setAttribute('data-kt-property-icon-bound', '1');
                }

                window.initPropertyIconPickers = function (scope) {
                    var rootEl = scope && scope.querySelectorAll ? scope : document;
                    rootEl.querySelectorAll('[data-kt-property-icon-picker]').forEach(bindPropertyIconPicker);
                };

                document.addEventListener('DOMContentLoaded', function () {
                    window.initPropertyIconPickers(document);
                });
            })();
        </script>
    @endpush
@endonce
