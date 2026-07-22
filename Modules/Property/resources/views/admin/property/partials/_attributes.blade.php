@php
    $groups = $attributeGroups ?? collect();
    $hasOldInput = fn (string $key): bool => session()->hasOldInput($key);
    $fieldValue = static function (array $attribute) use ($hasOldInput, $isEdit): mixed {
        $key = 'attributes.'.$attribute['code'];
        $value = $hasOldInput($key)
            ? old($key)
            : ($attribute['value'] ?? (! $isEdit ? $attribute['default_value'] : null));

        if ($value instanceof \DateTimeInterface) {
            return $attribute['type'] === 'datetime'
                ? $value->format('Y-m-d\TH:i')
                : $value->format('Y-m-d');
        }

        return $value;
    };
    $selectedIds = static fn (mixed $value): array => collect(is_array($value) ? $value : [$value])
        ->filter(fn ($id) => $id !== null && $id !== '')
        ->map(fn ($id) => (int) $id)
        ->all();
@endphp

@if($groups->isEmpty())
    <div class="text-muted fs-7 py-2">{{ __('Select one or more attribute groups to display their fields.') }}</div>
@else
    @foreach($groups as $group)
        @php
            $groupName = is_array($group['name'] ?? null)
                ? ($group['name'][app()->getLocale()] ?? reset($group['name']))
                : ($group['name'] ?? '');
        @endphp
        <section class="mb-7" aria-labelledby="attribute-group-{{ $group['id'] }}">
            @if($groups->count() > 1)
                <h4 class="fw-semibold fs-5 mb-4" id="attribute-group-{{ $group['id'] }}">{{ $groupName }}</h4>
            @endif
            <div class="row g-6">
            @foreach($group['attributes'] as $attribute)
                @php
                    $code = $attribute['code'];
                    $type = $attribute['type'];
                    $name = "attributes[{$code}]";
                    $dotName = "attributes.{$code}";
                    $inputId = "property-attribute-{$attribute['id']}";
                    $value = $fieldValue($attribute);
                    $ids = $selectedIds($value);
                    $error = $errors->first($dotName) ?: $errors->first($dotName.'.*');
                    $columnClass = in_array($type, ['textarea', 'checkbox', 'gallery'], true)
                        ? 'col-12'
                        : 'col-lg-6';
                    $attributeName = is_array($attribute['name'] ?? null)
                        ? ($attribute['name'][app()->getLocale()] ?? reset($attribute['name']))
                        : ($attribute['name'] ?? '');
                    $helpText = is_array($attribute['help_text'] ?? null)
                        ? ($attribute['help_text'][app()->getLocale()] ?? reset($attribute['help_text']) ?: null)
                        : ($attribute['help_text'] ?? null);
                @endphp

                <div class="{{ $columnClass }}">
                    <div class="fv-row">
                        <label class="form-label fw-semibold" for="{{ $inputId }}">
                            {{ $attributeName }}
                            @if($attribute['is_required'])
                                <span class="text-danger" aria-hidden="true">*</span>
                                <span class="visually-hidden">{{ __('Required') }}</span>
                            @endif
                        </label>

                        @if($helpText)
                            <div class="text-muted fs-7 mb-2" id="{{ $inputId }}-help">
                                {{ $helpText }}
                            </div>
                        @endif

                        @switch($type)
                            @case('text')
                                <input type="text" id="{{ $inputId }}" name="{{ $name }}"
                                       value="{{ $value }}" class="form-control form-control-solid @if($error) is-invalid @endif"
                                       @if($helpText) aria-describedby="{{ $inputId }}-help" @endif>
                                @break

                            @case('textarea')
                                <textarea id="{{ $inputId }}" name="{{ $name }}" rows="4"
                                          class="form-control form-control-solid @if($error) is-invalid @endif"
                                          @if($helpText) aria-describedby="{{ $inputId }}-help" @endif>{{ $value }}</textarea>
                                @break

                            @case('number')
                            @case('price')
                                <input type="number" id="{{ $inputId }}" name="{{ $name }}" step="any"
                                       value="{{ $value }}" class="form-control form-control-solid @if($error) is-invalid @endif"
                                       @if($helpText) aria-describedby="{{ $inputId }}-help" @endif>
                                @break

                            @case('boolean')
                                <input type="hidden" name="{{ $name }}" value="0">
                                <div class="form-check form-switch form-check-custom form-check-solid">
                                    <input type="checkbox" id="{{ $inputId }}" name="{{ $name }}" value="1"
                                           class="form-check-input @if($error) is-invalid @endif"
                                           @checked(filter_var($value, FILTER_VALIDATE_BOOLEAN))>
                                    <label class="form-check-label" for="{{ $inputId }}">{{ __('Yes') }}</label>
                                </div>
                                @break

                            @case('checkbox')
                                <input type="hidden" name="attributes_present[{{ $code }}]" value="1">
                                <div class="d-flex flex-wrap gap-4" id="{{ $inputId }}">
                                    @foreach($attribute['options'] as $option)
                                        @if($option->is_active || in_array((int) $option->id, $ids, true))
                                            <div class="form-check form-check-custom form-check-solid">
                                                <input type="checkbox" class="form-check-input"
                                                       id="{{ $inputId }}-{{ $option->id }}"
                                                       name="{{ $name }}[]" value="{{ $option->id }}"
                                                       @checked(in_array((int) $option->id, $ids, true))>
                                                <label class="form-check-label" for="{{ $inputId }}-{{ $option->id }}">
                                                    {{ $option->label }}
                                                    @if(! $option->is_active)
                                                        <span class="text-muted">({{ __('Inactive') }})</span>
                                                    @endif
                                                </label>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                                @break

                            @case('radio')
                                <div class="d-flex flex-wrap gap-4" id="{{ $inputId }}">
                                    @if(! $attribute['is_required'])
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input type="radio" class="form-check-input"
                                                   id="{{ $inputId }}-none" name="{{ $name }}" value=""
                                                   @checked($ids === [])>
                                            <label class="form-check-label" for="{{ $inputId }}-none">
                                                {{ __('None') }}
                                            </label>
                                        </div>
                                    @endif
                                    @foreach($attribute['options'] as $option)
                                        @if($option->is_active || in_array((int) $option->id, $ids, true))
                                            <div class="form-check form-check-custom form-check-solid">
                                                <input type="radio" class="form-check-input"
                                                       id="{{ $inputId }}-{{ $option->id }}"
                                                       name="{{ $name }}" value="{{ $option->id }}"
                                                       @checked(in_array((int) $option->id, $ids, true))>
                                                <label class="form-check-label" for="{{ $inputId }}-{{ $option->id }}">
                                                    {{ $option->label }}
                                                    @if(! $option->is_active)
                                                        <span class="text-muted">({{ __('Inactive') }})</span>
                                                    @endif
                                                </label>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                                @break

                            @case('select')
                            @case('multiselect')
                                @if($type === 'multiselect')
                                    <input type="hidden" name="attributes_present[{{ $code }}]" value="1">
                                @endif
                                <select id="{{ $inputId }}" name="{{ $name }}{{ $type === 'multiselect' ? '[]' : '' }}"
                                        class="form-select form-select-solid @if($error) is-invalid @endif"
                                        data-control="select2" data-allow-clear="true"
                                        @if($type === 'multiselect') multiple @endif>
                                    @if($type === 'select')
                                        <option value="">{{ __('Select') }}</option>
                                    @endif
                                    @foreach($attribute['options'] as $option)
                                        @if($option->is_active || in_array((int) $option->id, $ids, true))
                                            <option value="{{ $option->id }}"
                                                    @selected(in_array((int) $option->id, $ids, true))>
                                                {{ $option->label }}{{ ! $option->is_active ? ' ('.__('Inactive').')' : '' }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                                @break

                            @case('image')
                                @php
                                    $selectedMedia = old("attribute_media_path.{$code}");
                                    $currentImage = $selectedMedia ?: (is_string($value) ? $value : null);
                                    $previewUrl = $currentImage ? asset('storage/'.$currentImage) : asset('images/default.jpg');
                                    $previewId = "{$inputId}-preview";
                                @endphp
                                <div class="border rounded p-4">
                                    <div id="{{ $previewId }}" role="img" aria-label="{{ $attributeName }}"
                                         class="rounded border mb-3"
                                         style="width: 160px; height: 120px; background: center / cover no-repeat url('{{ $previewUrl }}')"></div>
                                    <input type="file" id="{{ $inputId }}" name="{{ $name }}" accept="image/*"
                                           class="form-control @if($error) is-invalid @endif">
                                    <input type="hidden" name="attribute_media_path[{{ $code }}]" value="{{ $selectedMedia }}">
                                    <input type="hidden" name="attributes_remove[{{ $code }}]" value="0">
                                    <div class="d-flex flex-wrap gap-2 mt-3">
                                        <button type="button" class="btn btn-sm btn-light-primary"
                                                data-media-picker-target="[name='attribute_media_path[{{ $code }}]']"
                                                data-media-preview-target="#{{ $previewId }}">
                                            {{ __('Choose from library') }}
                                        </button>
                                        @if($currentImage)
                                            <button type="button" class="btn btn-sm btn-light-danger"
                                                    data-attribute-media-remove="{{ $code }}"
                                                    data-preview-target="#{{ $previewId }}">
                                                {{ __('Remove') }}
                                            </button>
                                        @endif
                                    </div>
                                </div>
                                @break

                            @case('gallery')
                                @php
                                    $galleryOldKey = "attribute_gallery_existing.{$code}";
                                    $galleryPaths = $hasOldInput($galleryOldKey)
                                        ? (array) old($galleryOldKey, [])
                                        : (is_array($value) ? $value : []);
                                @endphp
                                <div class="js-attribute-gallery" data-gallery-code="{{ $code }}">
                                    <ul class="list-group mb-3 js-gallery-list" aria-label="{{ $attributeName }}">
                                        @foreach($galleryPaths as $path)
                                            <li class="list-group-item d-flex align-items-center gap-3 js-gallery-item">
                                                <input type="hidden" name="attribute_gallery_existing[{{ $code }}][]" value="{{ $path }}">
                                                <img src="{{ asset('storage/'.$path) }}" width="72" height="54"
                                                     class="rounded border object-fit-cover" alt="">
                                                <span class="text-break flex-grow-1">{{ $path }}</span>
                                                <div class="btn-group btn-group-sm" role="group" aria-label="{{ __('Gallery item controls') }}">
                                                    <button type="button" class="btn btn-light js-gallery-up" aria-label="{{ __('Move up') }}">↑</button>
                                                    <button type="button" class="btn btn-light js-gallery-down" aria-label="{{ __('Move down') }}">↓</button>
                                                    <button type="button" class="btn btn-light-danger js-gallery-remove" aria-label="{{ __('Remove') }}">×</button>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                    <input type="file" id="{{ $inputId }}" name="{{ $name }}[]" accept="image/*"
                                           multiple class="form-control @if($error) is-invalid @endif">
                                </div>
                                @break

                            @case('file')
                                @php
                                    $selectedFile = old("attribute_media_path.{$code}");
                                    $currentFile = $selectedFile ?: (is_string($value) ? $value : null);
                                @endphp
                                <input type="file" id="{{ $inputId }}" name="{{ $name }}"
                                       class="form-control @if($error) is-invalid @endif">
                                <input type="hidden" name="attribute_media_path[{{ $code }}]" value="{{ $selectedFile }}">
                                <input type="hidden" name="attributes_remove[{{ $code }}]" value="0">
                                @if($currentFile)
                                    <div class="d-flex align-items-center gap-3 mt-3">
                                        <a href="{{ asset('storage/'.$currentFile) }}" target="_blank" rel="noopener">
                                            {{ __('Open current file') }}
                                        </a>
                                        <button type="button" class="btn btn-sm btn-light-danger"
                                                data-attribute-media-remove="{{ $code }}">
                                            {{ __('Remove') }}
                                        </button>
                                    </div>
                                @endif
                                @break

                            @case('date')
                                <input type="date" id="{{ $inputId }}" name="{{ $name }}" value="{{ $value }}"
                                       class="form-control form-control-solid @if($error) is-invalid @endif">
                                @break

                            @case('datetime')
                                <input type="datetime-local" id="{{ $inputId }}" name="{{ $name }}" value="{{ $value }}"
                                       class="form-control form-control-solid @if($error) is-invalid @endif">
                                @break
                        @endswitch

                        @if($error)
                            <div class="invalid-feedback d-block">{{ $error }}</div>
                        @endif
                    </div>
                </div>
            @endforeach
            </div>
        </section>
    @endforeach

@endif
