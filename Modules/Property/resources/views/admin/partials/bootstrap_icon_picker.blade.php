{{--
    $name — input name (default: icon)
    $iconChoices — array<int, array{class: string, label: string}>
    $selected — full Bootstrap Icons class string e.g. bi bi-house
--}}
@php
    $inputName = $name ?? 'icon';
    $pickerUid = $pickerId ?? 'pi_'.uniqid();
@endphp

<div class="property-icon-picker w-100" id="{{ $pickerUid }}" data-kt-property-icon-picker>
    <div class="dropdown w-100">
        <button class="btn btn-light btn-active-light-primary border border-gray-300 d-flex align-items-center justify-content-between gap-3 w-100 px-4 py-3 text-start"
                type="button"
                id="{{ $pickerUid }}_toggle"
                data-bs-toggle="dropdown"
                data-bs-auto-close="outside"
                aria-expanded="false"
                aria-label="{{ __('Choose icon') }}">
            <span class="d-flex align-items-center gap-3 min-w-0">
                <i data-kt-property-icon-preview class="{{ $selected }} fs-2 text-primary flex-shrink-0"
                   aria-hidden="true"></i>
            </span>
            <i class="bi bi-chevron-down text-gray-600 flex-shrink-0"></i>
        </button>
        <div class="dropdown-menu w-100 p-3 shadow-sm mt-1"
             aria-labelledby="{{ $pickerUid }}_toggle"
             style="max-height: min(320px, 50vh); overflow-y: auto;">
            <div class="row g-2">
                @foreach($iconChoices as $opt)
                    @php $isActive = ($selected === $opt['class']); @endphp
                    <div class="col-4 col-sm-3 col-md-2">
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
    <input type="hidden" name="{{ $inputName }}" value="{{ $selected }}" required data-kt-property-icon-input>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('[data-kt-property-icon-picker]').forEach(function (root) {
                    var input = root.querySelector('[data-kt-property-icon-input]');
                    var preview = root.querySelector('[data-kt-property-icon-preview]');
                    if (!input || !preview) {
                        return;
                    }

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
                            var icon = this.getAttribute('data-kt-property-icon-value');
                            input.value = icon;
                            preview.className = icon + ' fs-2 text-primary flex-shrink-0';
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
                });
            });
        </script>
    @endpush
@endonce
