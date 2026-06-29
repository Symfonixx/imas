@props([
    'inputId',
    'name' => 'mobile',
    'value' => '',
    'label' => null,
    'required' => false,
])

@php
    $displayLabel = $label ?? __('Mobile');
    $storedValue = old($name, $value);
    $countriesCollection = $countries ?? collect();
    $countriesPayload = $countriesCollection
        ->filter(static fn ($c) => (string) ($c->phone_code ?? '') !== '' && (int) $c->phone_code !== 0)
        ->map(static fn ($c) => [
            'id' => $c->id,
            'name' => $c->name,
            'iso_code_2' => $c->iso_code_2,
            'phone_code' => $c->phone_code,
            'flag' => $c->flag,
        ])
        ->values();
    $defaultIso = match (app()->getLocale()) {
        'tr' => 'TR',
        'ar' => 'SA',
        default => 'US',
    };
@endphp

<label for="{{ $inputId }}" class="form-label {{ $required ? 'required' : '' }}">{{ $displayLabel }}</label>
<div
    class="admin-phone-field @error($name) is-invalid @enderror"
    data-admin-phone-field
    data-input-id="{{ $inputId }}"
    data-default-iso="{{ $defaultIso }}"
    data-countries='@json($countriesPayload)'
    data-initial-value="{{ $storedValue }}"
    dir="ltr"
>
    <input type="hidden" name="{{ $name }}" value="{{ $storedValue }}" data-admin-phone-hidden>
    <div class="admin-phone-field__country-shell" data-admin-phone-country-shell>
        <button
            type="button"
            id="{{ $inputId }}-country-code"
            class="admin-phone-field__country-trigger"
            data-admin-phone-country-trigger
            aria-haspopup="listbox"
            aria-expanded="false"
            aria-label="{{ __('Country code') }}"
        >
            <img
                class="admin-phone-field__flag"
                data-admin-phone-flag
                src="{{ asset('images/flags/flag.svg') }}"
                alt=""
                width="22"
                height="16"
                decoding="async"
                loading="lazy"
            />
            <span class="admin-phone-field__code" data-admin-phone-code-label aria-hidden="true">+—</span>
        </button>
        <div class="admin-phone-field__dropdown" data-admin-phone-dropdown hidden>
            <div class="admin-phone-field__search-wrap">
                <input
                    type="search"
                    class="form-control form-control-solid admin-phone-field__search"
                    data-admin-phone-search
                    placeholder="{{ __('Search') }}"
                    autocomplete="off"
                    spellcheck="false"
                />
            </div>
            <ul class="admin-phone-field__options" role="listbox" data-admin-phone-options></ul>
        </div>
    </div>
    <span class="admin-phone-field__sep" aria-hidden="true"></span>
    <input
        type="tel"
        id="{{ $inputId }}"
        class="admin-phone-field__national form-control form-control-solid"
        data-admin-phone-national
        inputmode="numeric"
        autocomplete="tel-national"
        placeholder="{{ __('National number (no country code)') }}"
        @if($required) required @endif
    />
</div>
@error($name)
    <span class="invalid-feedback d-block" role="alert">
        <strong>{{ $message }}</strong>
    </span>
@enderror

@once
    @push('scripts')
        <style>
            .admin-phone-field {
                display: flex;
                align-items: stretch;
                width: 100%;
                border: 1px solid var(--bs-gray-300, #e4e6ef);
                border-radius: 0.475rem;
                background: var(--bs-gray-100, #f5f8fa);
                overflow: visible;
                position: relative;
            }

            .admin-phone-field.is-invalid {
                border-color: var(--bs-danger, #f1416c);
            }

            .admin-phone-field__country-shell {
                position: relative;
                flex: 0 0 auto;
                min-width: 6.75rem;
                max-width: 46%;
            }

            .admin-phone-field__country-trigger {
                display: flex;
                align-items: center;
                gap: 8px;
                width: 100%;
                height: 100%;
                min-height: 100%;
                padding: 0.65rem 1.75rem 0.65rem 0.75rem;
                border: none;
                border-radius: 0.475rem 0 0 0.475rem;
                background: transparent;
                background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23a1a5b7' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
                background-repeat: no-repeat;
                background-position: right 10px center;
                background-size: 10px;
                cursor: pointer;
                color: var(--bs-gray-700, #5e6278);
            }

            .admin-phone-field__country-trigger:focus {
                outline: none;
            }

            .admin-phone-field__country-trigger:focus-visible {
                box-shadow: inset 0 0 0 2px var(--bs-primary, #009ef7);
            }

            .admin-phone-field__dropdown {
                position: absolute;
                top: calc(100% + 4px);
                left: 0;
                right: 0;
                min-width: 100%;
                max-height: min(280px, 48vh);
                display: flex;
                flex-direction: column;
                background: #fff;
                border: 1px solid var(--bs-gray-300, #e4e6ef);
                border-radius: 0.475rem;
                box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
                z-index: 1060;
                overflow: hidden;
            }

            .admin-phone-field__search-wrap {
                padding: 6px;
                border-bottom: 1px solid var(--bs-gray-200, #eff2f5);
            }

            .admin-phone-field__search {
                font-size: 0.925rem;
            }

            .admin-phone-field__options {
                flex: 1;
                min-height: 0;
                margin: 0;
                padding: 6px 0;
                list-style: none;
                overflow-y: auto;
            }

            .admin-phone-field__option {
                display: flex;
                align-items: center;
                gap: 8px;
                padding: 0.5rem 0.75rem;
                cursor: pointer;
                color: var(--bs-gray-800, #3f4254);
                font-size: 0.925rem;
            }

            .admin-phone-field__option:hover,
            .admin-phone-field__option--selected {
                background: var(--bs-gray-100, #f5f8fa);
            }

            .admin-phone-field__option--empty {
                cursor: default;
                color: var(--bs-gray-500, #a1a5b7);
            }

            .admin-phone-field__flag {
                flex-shrink: 0;
                width: 22px;
                height: 16px;
                object-fit: cover;
                border-radius: 2px;
            }

            .admin-phone-field__code {
                flex-shrink: 0;
                font-size: 0.925rem;
                color: var(--bs-gray-600, #7e8299);
            }

            .admin-phone-field__sep {
                width: 1px;
                align-self: stretch;
                background: var(--bs-gray-300, #e4e6ef);
                flex-shrink: 0;
            }

            .admin-phone-field__national {
                flex: 1 1 120px;
                min-width: 0;
                border: none !important;
                border-radius: 0 0.475rem 0.475rem 0 !important;
                background: transparent !important;
                box-shadow: none !important;
            }

            .admin-phone-field__national:focus {
                box-shadow: none !important;
            }

            .admin-phone-field:focus-within {
                border-color: var(--bs-primary, #009ef7);
                box-shadow: 0 0 0 0.2rem rgba(0, 158, 247, 0.15);
            }

            .admin-phone-field--open {
                z-index: 1055;
            }
        </style>
        <script>
            (function () {
                function digitsOnly(value) {
                    return String(value ?? '').replace(/\D/g, '');
                }

                function displayCallingCode(phoneCode) {
                    var digits = digitsOnly(phoneCode);
                    return digits || '—';
                }

                function normalizeNationalDigits(raw) {
                    var value = digitsOnly(raw);
                    while (value.charAt(0) === '0') {
                        value = value.slice(1);
                    }
                    return value;
                }

                function parseMobile(mobile, countries) {
                    var digits = digitsOnly(mobile);
                    if (!digits) {
                        return { countryId: null, national: '' };
                    }

                    var sorted = countries.slice().sort(function (a, b) {
                        return digitsOnly(b.phone_code).length - digitsOnly(a.phone_code).length;
                    });

                    for (var i = 0; i < sorted.length; i++) {
                        var country = sorted[i];
                        var code = digitsOnly(country.phone_code);
                        if (code && digits.indexOf(code) === 0) {
                            return {
                                countryId: country.id,
                                national: digits.slice(code.length),
                            };
                        }
                    }

                    return { countryId: null, national: digits };
                }

                function filterCountries(countries, query) {
                    var raw = String(query ?? '').trim();
                    if (!raw) {
                        return countries;
                    }

                    var qDigits = digitsOnly(raw);
                    var alphaQuery = raw.replace(/[\d+()\-\s]/g, '').trim().toLowerCase();

                    return countries.filter(function (country) {
                        var codeDigits = digitsOnly(country.phone_code);
                        if (qDigits.length > 0 && codeDigits.indexOf(qDigits) === 0) {
                            return true;
                        }
                        if (alphaQuery.length > 0) {
                            var name = String(country.name ?? '').toLowerCase();
                            var iso = String(country.iso_code_2 ?? '').toLowerCase();
                            return name.indexOf(alphaQuery) !== -1 || iso.indexOf(alphaQuery) !== -1;
                        }
                        return false;
                    });
                }

                function initPhoneField(root) {
                    if (!root || root.dataset.adminPhoneReady === '1') {
                        return;
                    }
                    root.dataset.adminPhoneReady = '1';

                    var countries = [];
                    try {
                        countries = JSON.parse(root.getAttribute('data-countries') || '[]');
                    } catch (e) {
                        countries = [];
                    }

                    var defaultIso = root.getAttribute('data-default-iso') || 'TR';
                    var hiddenInput = root.querySelector('[data-admin-phone-hidden]');
                    var nationalInput = root.querySelector('[data-admin-phone-national]');
                    var trigger = root.querySelector('[data-admin-phone-country-trigger]');
                    var dropdown = root.querySelector('[data-admin-phone-dropdown]');
                    var searchInput = root.querySelector('[data-admin-phone-search]');
                    var optionsList = root.querySelector('[data-admin-phone-options]');
                    var flagImg = root.querySelector('[data-admin-phone-flag]');
                    var codeLabel = root.querySelector('[data-admin-phone-code-label]');
                    var countryShell = root.querySelector('[data-admin-phone-country-shell]');

                    var selectedCountryId = null;
                    var dropdownOpen = false;

                    function findCountry(id) {
                        for (var i = 0; i < countries.length; i++) {
                            if (countries[i].id === id) {
                                return countries[i];
                            }
                        }
                        return null;
                    }

                    function pickDefaultCountry() {
                        if (selectedCountryId != null && findCountry(selectedCountryId)) {
                            return;
                        }
                        var found = null;
                        for (var i = 0; i < countries.length; i++) {
                            if (countries[i].iso_code_2 === defaultIso) {
                                found = countries[i];
                                break;
                            }
                        }
                        selectedCountryId = (found || countries[0] || {}).id ?? null;
                    }

                    function renderOptions() {
                        if (!optionsList) {
                            return;
                        }
                        var filtered = filterCountries(countries, searchInput ? searchInput.value : '');
                        optionsList.innerHTML = '';

                        if (!filtered.length) {
                            var empty = document.createElement('li');
                            empty.className = 'admin-phone-field__option admin-phone-field__option--empty';
                            empty.textContent = @json(__('No countries match your search.'));
                            optionsList.appendChild(empty);
                            return;
                        }

                        filtered.forEach(function (country) {
                            var item = document.createElement('li');
                            item.className = 'admin-phone-field__option';
                            if (country.id === selectedCountryId) {
                                item.className += ' admin-phone-field__option--selected';
                            }
                            item.setAttribute('role', 'option');
                            item.setAttribute('aria-selected', country.id === selectedCountryId ? 'true' : 'false');

                            if (country.flag) {
                                var img = document.createElement('img');
                                img.className = 'admin-phone-field__flag';
                                img.src = country.flag;
                                img.alt = '';
                                img.width = 22;
                                img.height = 16;
                                item.appendChild(img);
                            }

                            var code = document.createElement('span');
                            code.className = 'admin-phone-field__code';
                            code.textContent = '+' + displayCallingCode(country.phone_code);
                            item.appendChild(code);

                            item.addEventListener('click', function (event) {
                                event.preventDefault();
                                selectedCountryId = country.id;
                                setDropdownOpen(false);
                                syncHidden();
                                updateTrigger();
                                renderOptions();
                            });

                            optionsList.appendChild(item);
                        });
                    }

                    function updateTrigger() {
                        var country = findCountry(selectedCountryId);
                        if (!country) {
                            if (flagImg) {
                                flagImg.src = @json(asset('images/flags/flag.svg'));
                            }
                            if (codeLabel) {
                                codeLabel.textContent = '+—';
                            }
                            if (trigger) {
                                trigger.setAttribute('aria-label', @json(__('Country code')));
                            }
                            return;
                        }

                        if (flagImg && country.flag) {
                            flagImg.src = country.flag;
                        }
                        if (codeLabel) {
                            codeLabel.textContent = '+' + displayCallingCode(country.phone_code);
                        }
                        if (trigger) {
                            var iso = String(country.iso_code_2 || '').toUpperCase();
                            trigger.setAttribute(
                                'aria-label',
                                @json(__('Country code')) + ': +' + displayCallingCode(country.phone_code) + (iso ? ', ' + iso : '')
                            );
                        }
                    }

                    function buildPayload() {
                        var country = findCountry(selectedCountryId);
                        var code = country ? digitsOnly(country.phone_code) : '';
                        var national = normalizeNationalDigits(nationalInput ? nationalInput.value : '');
                        if (!national) {
                            return '';
                        }
                        return code + national;
                    }

                    function syncHidden() {
                        if (!hiddenInput) {
                            return;
                        }
                        hiddenInput.value = buildPayload();
                    }

                    function setDropdownOpen(open) {
                        dropdownOpen = open;
                        root.classList.toggle('admin-phone-field--open', open);
                        if (dropdown) {
                            dropdown.hidden = !open;
                        }
                        if (trigger) {
                            trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
                        }
                        if (open) {
                            renderOptions();
                            if (searchInput) {
                                searchInput.value = '';
                                setTimeout(function () {
                                    searchInput.focus();
                                }, 0);
                            }
                        }
                    }

                    function applyInitialValue() {
                        var initial = root.getAttribute('data-initial-value') || '';
                        var parsed = parseMobile(initial, countries);
                        if (parsed.countryId != null) {
                            selectedCountryId = parsed.countryId;
                        } else {
                            pickDefaultCountry();
                        }
                        if (nationalInput) {
                            nationalInput.value = parsed.national;
                        }
                        updateTrigger();
                        syncHidden();
                    }

                    pickDefaultCountry();
                    applyInitialValue();

                    if (nationalInput) {
                        nationalInput.addEventListener('input', syncHidden);
                    }

                    if (trigger) {
                        trigger.addEventListener('click', function (event) {
                            event.stopPropagation();
                            setDropdownOpen(!dropdownOpen);
                        });
                    }

                    if (searchInput) {
                        searchInput.addEventListener('input', renderOptions);
                        searchInput.addEventListener('keydown', function (event) {
                            if (event.key === 'Escape') {
                                setDropdownOpen(false);
                            }
                        });
                    }

                    root.closest('form')?.addEventListener('submit', syncHidden);

                    document.addEventListener('pointerdown', function (event) {
                        if (!dropdownOpen) {
                            return;
                        }
                        if (countryShell && !countryShell.contains(event.target)) {
                            setDropdownOpen(false);
                        }
                    });

                    document.addEventListener('keydown', function (event) {
                        if (event.key === 'Escape') {
                            setDropdownOpen(false);
                        }
                    });
                }

                function initAllPhoneFields() {
                    document.querySelectorAll('[data-admin-phone-field]').forEach(initPhoneField);
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initAllPhoneFields);
                } else {
                    initAllPhoneFields();
                }
            })();
        </script>
    @endpush
@endonce
