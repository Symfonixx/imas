@php
    $attribute = $attribute ?? null;
    $editing = isset($attribute);
    $selectedType = old('type', $attribute?->type->value ?? \Modules\Property\Enums\AttributeType::Text->value);
    $selectedAttributeType = \Modules\Property\Enums\AttributeType::tryFrom($selectedType)
        ?? \Modules\Property\Enums\AttributeType::Text;
    $optionsVisible = $selectedAttributeType->hasOptions();
    $defaultVisible = ! $optionsVisible && ! $selectedAttributeType->isMedia();
    $iconChoices = config('property.bootstrap_icons', []);
    $optionRows = old('options');
    if ($optionRows === null) {
        $optionRows = $editing
            ? $attribute->options->map(fn ($option) => [
                'id' => $option->id,
                'label' => $option->label,
                'icon' => $option->icon,
                'is_active' => $option->is_active,
            ])->all()
            : [];
    }
@endphp

<div class="row gx-5 gx-xl-10">
    <div class="col-xl-8">
        <div class="card card-flush mb-7">
            <div class="card-header"><div class="card-title"><h2>{{ __('Attribute definition') }}</h2></div></div>
            <div class="card-body pt-3">
                <x-admin.form-group label="Code" name="code" required
                                    helper="{{ __('Permanent machine name. Lowercase letters, numbers, and underscores.') }}">
                    <input class="form-control form-control-solid" name="code"
                           value="{{ old('code', $attribute->code ?? '') }}"
                           pattern="[a-z][a-z0-9_]*" maxlength="100"
                           @readonly($editing) required/>
                </x-admin.form-group>

                <x-admin.form-group label="Name" name="name" required translatable>
                    <input class="form-control form-control-solid" name="name"
                           value="{{ old('name', $attribute->name ?? '') }}" maxlength="255" required/>
                </x-admin.form-group>

                <x-admin.form-group label="Help text" name="help_text" translatable>
                    <textarea class="form-control form-control-solid" name="help_text"
                              rows="3">{{ old('help_text', $attribute->help_text ?? '') }}</textarea>
                </x-admin.form-group>

                <x-admin.form-group label="Image" name="img"
                                    helper="{{ __('Optional icon shown at 36×36. Choose from the Media Library.') }}">
                    <x-admin.image-input
                        name="img"
                        size="36px"
                        :preview="$editing && $attribute->image_link ? $attribute->image_link : null"
                        :mediaPath="$editing ? ($attribute->image ?? null) : null"/>
                </x-admin.form-group>

                <x-admin.form-group label="Type" name="type" required>
                    <select class="form-select form-select-solid" name="type" id="attribute_type" required>
                        @foreach($types as $type)
                            <option value="{{ $type->value }}" @selected($selectedType === $type->value)>
                                {{ __(ucfirst($type->value)) }}
                            </option>
                        @endforeach
                    </select>
                </x-admin.form-group>

                <div class="row">
                    <div class="col-md-6">
                        <x-admin.form-group label="Validation rule" name="validation">
                            <select class="form-select form-select-solid" name="validation">
                                <option value="">{{ __('None') }}</option>
                                @foreach($validationChoices as $choice)
                                    <option value="{{ $choice }}" @selected(old('validation', $attribute->validation ?? '') === $choice)>
                                        {{ __(str_replace('_', ' ', ucfirst($choice))) }}
                                    </option>
                                @endforeach
                            </select>
                        </x-admin.form-group>
                    </div>
                    <div class="col-md-6">
                        <x-admin.form-group label="Regular expression" name="regex">
                            <input class="form-control form-control-solid" name="regex"
                                   value="{{ old('regex', $attribute->regex ?? '') }}" placeholder="/pattern/i"/>
                        </x-admin.form-group>
                    </div>
                </div>

                <div id="attribute_default_value" @class(['d-none' => ! $defaultVisible])>
                    <x-admin.form-group label="Default value" name="default_value"
                                        helper="{{ __('Defaults are available for scalar, boolean, number, date, and date/time attributes.') }}">
                        <input class="form-control form-control-solid" name="default_value"
                               value="{{ old('default_value', data_get($attribute?->default_value, 'value', '')) }}"
                               @disabled(! $defaultVisible)/>
                    </x-admin.form-group>
                </div>
            </div>
        </div>

        <div @class(['card card-flush mb-7', 'd-none' => ! $optionsVisible]) id="attribute_options_card">
            <div class="card-header">
                <div class="card-title"><h2>{{ __('Options') }}</h2></div>
                <div class="card-toolbar">
                    <button type="button" class="btn btn-sm btn-light-primary" id="add_attribute_option"
                            @disabled(! $optionsVisible)>
                        {{ __('Add option') }}
                    </button>
                </div>
            </div>
            <div class="card-body pt-3">
                @error('options')<div class="text-danger mb-3">{{ $message }}</div>@enderror
                <div id="attribute_options" class="d-flex flex-column gap-3">
                    @foreach($optionRows as $index => $option)
                        @php
                            $optionIcon = trim((string) ($option['icon'] ?? ''));
                            $optionIconChoices = collect($iconChoices);
                            if ($optionIcon !== '' && ! $optionIconChoices->pluck('class')->contains($optionIcon)) {
                                $optionIconChoices = $optionIconChoices->prepend([
                                    'class' => $optionIcon,
                                    'label' => __('Current icon'),
                                ]);
                            }
                        @endphp
                        <div class="attribute-option d-flex align-items-center gap-2 flex-wrap" draggable="true">
                            <span class="text-muted cursor-move" aria-hidden="true">⋮⋮</span>
                            @if(! empty($option['id']))
                                <input type="hidden" data-field="id" name="options[{{ $index }}][id]"
                                       value="{{ $option['id'] }}" @disabled(! $optionsVisible)/>
                            @endif
                            @include('property::admin.partials.bootstrap_icon_picker', [
                                'name' => 'options['.$index.'][icon]',
                                'iconChoices' => $optionIconChoices->values()->all(),
                                'selected' => $optionIcon,
                                'required' => false,
                                'compact' => true,
                                'pickerId' => 'attr_opt_icon_'.$index,
                            ])
                            <input class="form-control form-control-solid flex-grow-1" data-field="label"
                                   name="options[{{ $index }}][label]" value="{{ $option['label'] ?? '' }}"
                                   aria-label="{{ __('Option label') }}" @disabled(! $optionsVisible)
                                   style="min-width: 10rem;"/>
                            <label class="form-check form-switch form-check-custom form-check-solid">
                                <input type="hidden" data-field="is_active" name="options[{{ $index }}][is_active]"
                                       value="0" @disabled(! $optionsVisible)/>
                                <input class="form-check-input" type="checkbox" data-field="is_active"
                                       name="options[{{ $index }}][is_active]" value="1"
                                       @checked((bool) ($option['is_active'] ?? false))
                                       @disabled(! $optionsVisible)/>
                                <span class="form-check-label">{{ __('Active') }}</span>
                            </label>
                            <button type="button" class="btn btn-sm btn-icon btn-light move-up"
                                    aria-label="{{ __('Move option up') }}" @disabled(! $optionsVisible)>↑</button>
                            <button type="button" class="btn btn-sm btn-icon btn-light move-down"
                                    aria-label="{{ __('Move option down') }}" @disabled(! $optionsVisible)>↓</button>
                            <button type="button" class="btn btn-sm btn-icon btn-light-danger remove-option"
                                    aria-label="{{ __('Remove option') }}" @disabled(! $optionsVisible)>×</button>
                        </div>
                    @endforeach
                </div>
                <template id="attribute_option_icon_picker_template">
                    @include('property::admin.partials.bootstrap_icon_picker', [
                        'name' => 'options[__INDEX__][icon]',
                        'iconChoices' => $iconChoices,
                        'selected' => '',
                        'required' => false,
                        'compact' => true,
                        'pickerId' => 'attr_opt_icon_tpl',
                    ])
                </template>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card card-flush mb-7">
            <div class="card-header"><div class="card-title"><h2>{{ __('Settings') }}</h2></div></div>
            <div class="card-body pt-3 d-flex flex-column gap-5">
                @foreach([
                    'is_required' => ['Required', $attribute->is_required ?? false],
                    'is_unique' => ['Unique', $attribute->is_unique ?? false],
                    'is_active' => ['Active', $attribute->is_active ?? true],
                ] as $field => [$label, $default])
                    <label class="form-check form-switch form-check-custom form-check-solid">
                        <input type="hidden" name="{{ $field }}" value="0"/>
                        <input class="form-check-input" type="checkbox" name="{{ $field }}" value="1"
                               @checked((bool) old($field, $default))/>
                        <span class="form-check-label">{{ __($label) }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        @if($editing)
            <div class="card card-flush mb-7">
                <div class="card-header">
                    <div class="card-title">
                        <h2 class="d-flex align-items-center">
                            <i class="bi bi-translate text-primary fs-3 me-2"></i>
                            {{ __('Update Other Languages') }}
                        </h2>
                    </div>
                </div>
                <div class="card-body pt-3">
                    <label class="form-check form-check-custom form-check-solid">
                        <input class="form-check-input"
                               type="checkbox"
                               name="update_translations"
                               value="1"
                               @checked(old('update_translations', false))/>
                        <span class="form-check-label fs-7 ms-2">
                            {{ __('Use Google Translate to update all other languages.') }}
                        </span>
                    </label>
                </div>
            </div>
        @endif
    </div>
</div>

<x-admin.form-actions :discard-url="route('admin.property_attributes.index')"/>
