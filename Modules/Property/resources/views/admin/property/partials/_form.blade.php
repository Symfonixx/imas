@php
    $isEdit = isset($property);
    $seoMeta = $isEdit ? ($property->metadata ?? []) : [];
    $defaultStatus = old('status', $isEdit ? (($property->status?->value) ?? 'Published') : 'Published');
    $editArea = $isEdit ? $property->location : null;
    $selectedAreaIdValue = old('location_id', $editArea?->id);
@endphp

<div class="card card-flush mb-7">
    <div class="card-body">
        <div class="stepper stepper-pills" id="property_stepper">
            <div class="stepper-nav property-stepper-nav mb-10">
                @for($step = 1; $step <= 6; $step++)
                    <div class="stepper-item {{ $step === 1 ? 'current' : '' }}" data-step="{{ $step }}">
                        <div class="stepper-wrapper">
                            <div class="stepper-icon w-40px h-40px">
                                <i class="stepper-check bi bi-check-lg"></i>
                                <span class="stepper-number">{{ $step }}</span>
                            </div>
                            <div class="stepper-label">
                                <h3 class="stepper-title">{{ __("Step $step") }}</h3>
                            </div>
                        </div>
                        @if($step < 6)
                            <div class="stepper-line h-40px"></div>
                        @endif
                    </div>
                @endfor
            </div>

            <div data-step-content="1">
                <div class="row mb-2">
                    <div class="col-xl-8">
                        <x-admin.form-group label="Title" name="title" required translatable>
                            <input id="title" type="text" name="title" class="form-control form-control-solid"
                                   value="{{ old('title', $property->title ?? '') }}"/>
                        </x-admin.form-group>
                        <x-admin.form-group label="Project name" name="project_name" required translatable>
                            <input type="text" name="project_name" class="form-control form-control-solid"
                                   value="{{ old('project_name', $property->project_name ?? '') }}"/>
                        </x-admin.form-group>
                        <x-admin.form-group label="Project code" name="project_code" required>
                            <input id="slug" type="text" name="project_code" class="form-control form-control-solid"
                                   value="{{ old('project_code', $property->project_code ?? '') }}"/>
                        </x-admin.form-group>
                        <x-admin.form-group label="Overview" name="overview" required translatable>
                            <textarea class="form-control form-control-solid" name="overview"
                                      rows="8">{{ old('overview', $property->overview ?? '') }}</textarea>
                        </x-admin.form-group>
                    </div>
                    <div class="col-xl-4">
                        <x-admin.form-group label="Thumbnail" name="thumbnail" required>
                            <x-admin.image-input name="thumbnail"
                                                 :preview="$isEdit ? ($property->thumbnail ? asset('storage/' . $property->thumbnail) : null) : null"/>
                        </x-admin.form-group>
                        <x-admin.form-group label="Status" name="status" required>
                            <select name="status" class="form-select form-select-solid" data-control="select2"
                                    data-hide-search="true">
                                @foreach($statuses as $status)
                                    <option value="{{ $status->value }}" @selected($defaultStatus === $status->value)>
                                        {{ __($status->value) }}
                                    </option>
                                @endforeach
                            </select>
                        </x-admin.form-group>
                        <x-admin.form-group label="Area" name="location_id" required>
                            <select id="location_id" name="location_id" class="form-select form-select-solid"
                                    data-control="select2" data-placeholder="{{ __('Select area') }}">
                                <option value="">{{ __('Select area') }}</option>
                                @foreach(($areas ?? []) as $area)
                                    <option
                                        value="{{ $area['id'] }}" @selected((string) $selectedAreaIdValue === (string) $area['id'])>{{ $area['label'] ?? $area['name'] }}</option>
                                @endforeach
                            </select>
                        </x-admin.form-group>
                    </div>
                </div>
            </div>

            <div class="d-none" data-step-content="2">
                <x-admin.form-group label="Property type" name="property_type_id" required>
                    <select name="property_type_id" id="property_type_id" class="form-select form-select-solid"
                            data-control="select2" data-placeholder="{{ __('Select property type') }}">
                        <option value="">{{ __('Select property type') }}</option>
                        @foreach($propertyTypes as $propertyType)
                            <option
                                value="{{ $propertyType['id'] }}" @selected((string) old('property_type_id', $property->property_type_id ?? '') === (string) $propertyType['id'])>
                                {{ $propertyType['name'] }}
                            </option>
                        @endforeach
                    </select>
                </x-admin.form-group>
                <div class="alert alert-info">
                    {{ __('Attributes are loaded from the selected property type family.') }}
                </div>
            </div>

            <div class="d-none" data-step-content="3">
                <div id="dynamic-attributes-container"></div>
            </div>

            <div class="d-none" data-step-content="4">
                <div class="row mb-2">
                    <div class="col-md-4">
                        <x-admin.form-group label="Price" name="price">
                            <input type="number" step="0.01" min="0" class="form-control form-control-solid"
                                   name="price" value="{{ old('price', $property->price ?? 0) }}"/>
                        </x-admin.form-group>
                    </div>
                    <div class="col-md-4">
                        <x-admin.form-group label="Min area" name="min_area">
                            <input type="number" step="0.01" min="0" class="form-control form-control-solid"
                                   name="min_area" value="{{ old('min_area', $property->min_area ?? '') }}"/>
                        </x-admin.form-group>
                    </div>
                    <div class="col-md-4">
                        <x-admin.form-group label="Max area" name="max_area">
                            <input type="number" step="0.01" min="0" class="form-control form-control-solid"
                                   name="max_area" value="{{ old('max_area', $property->max_area ?? '') }}"/>
                        </x-admin.form-group>
                    </div>
                </div>
            </div>

            <div class="d-none" data-step-content="5">
                <x-admin.form-group label="Why to buy" name="why_to_buy" required translatable>
                    <textarea class="form-control form-control-solid" name="why_to_buy"
                              rows="8">{{ old('why_to_buy', $property->why_to_buy ?? '') }}</textarea>
                </x-admin.form-group>
                <x-admin.form-group label="Facilities" name="facilities" translatable>
                    <textarea class="form-control form-control-solid" name="facilities"
                              rows="8">{{ old('facilities', $property->facilities ?? '') }}</textarea>
                </x-admin.form-group>
                <x-admin.form-group label="Content" name="content" required translatable>
                    <textarea id="tinymce" class="form-control form-control-solid"
                              name="content">{!! old('content', $property->content ?? '') !!}</textarea>
                </x-admin.form-group>
                <x-admin.form-group label="Youtube video url" name="youtube_video_url">
                    <input type="url" name="youtube_video_url" class="form-control form-control-solid"
                           value="{{ old('youtube_video_url', $property->youtube_video_url ?? '') }}"/>
                </x-admin.form-group>
                <div class="row mb-2">
                    <div class="col-md-6">
                        <x-admin.form-group label="Latitude" name="lat">
                            <input type="text" inputmode="decimal" name="lat" class="form-control form-control-solid"
                                   value="{{ old('lat', $property->lat ?? '') }}"/>
                        </x-admin.form-group>
                    </div>
                    <div class="col-md-6">
                        <x-admin.form-group label="Longitude" name="lng">
                            <input type="text" inputmode="decimal" name="lng" class="form-control form-control-solid"
                                   value="{{ old('lng', $property->lng ?? '') }}"/>
                        </x-admin.form-group>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-6 mb-2">
                        <x-admin.toggle-switch name="is_sold_out" label="Sold out"
                                               :checked="(bool) old('is_sold_out', $property->is_sold_out ?? false)"/>
                    </div>
                    <div class="col-md-6 mb-2">
                        <x-admin.toggle-switch name="is_recommended" label="Recommended"
                                               :checked="(bool) old('is_recommended', $property->is_recommended ?? false)"/>
                    </div>
                    <div class="col-md-6 mb-2">
                        <x-admin.toggle-switch name="is_citizenship_eligible" label="Citizenship eligible"
                                               :checked="(bool) old('is_citizenship_eligible', $property->is_citizenship_eligible ?? false)"/>
                    </div>
                    <div class="col-md-6 mb-2">
                        <x-admin.toggle-switch name="is_featured" label="Featured"
                                               :checked="(bool) old('is_featured', $property->is_featured ?? false)"
                                               last/>

                    </div>
                </div>

                <x-admin.form-group label="Property slides" name="slides" helper="{{ __('Maximum 20 images.') }}">
                    <div class="dropzone border-dashed border-primary rounded p-5 text-center"
                         id="property-slides-dropzone">
                        <div class="dz-message needsclick">
                            <i class="bi bi-cloud-upload fs-1 text-primary"></i>
                            <div class="ms-4">
                                <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __('Drop files here or click to upload.') }}</h3>
                                <span
                                    class="fs-7 fw-semibold text-gray-500">{{ __('You can upload up to 20 files.') }}</span>
                            </div>
                        </div>
                        <input type="file" name="slides[]" id="slides-input" class="d-none" accept="image/*" multiple>
                    </div>
                    <div class="text-muted fs-7 mt-2" id="slides-count-text">{{ __('No files selected.') }}</div>
                    @if($isEdit && $property->slides->isNotEmpty())
                        <div class="row mt-4">
                            @foreach($property->slides as $slide)
                                <div class="col-md-2 mb-3">
                                    <img src="{{ asset('storage/' . $slide->image) }}" class="img-fluid rounded border"
                                         alt="slide">
                                </div>
                            @endforeach
                        </div>
                    @endif
                </x-admin.form-group>
            </div>

            <div class="d-none" data-step-content="6">
                @include('cms::admin.partials._seo_section', [
                    'metaTitle' => old('meta_title', $seoMeta['meta_title'] ?? ''),
                    'metaDescription' => old('meta_description', $seoMeta['meta_description'] ?? ''),
                    'metaKeywords' => old('meta_keywords', implode(', ', $seoMeta['meta_keywords'] ?? [])),
                    'metaImagePreview' => null,
                    'titleSource' => '#title',
                    'descSource' => '#meta_description',
                    'slugSource' => '#slug',
                    'baseUrl' => url('/') . '/properties/',
                ])

                <x-admin.form-group label="Schema JSON-LD" name="meta_schema">
                    <textarea class="form-control form-control-solid" rows="5"
                              name="meta_schema">{{ old('meta_schema', $seoMeta['schema'] ?? '') }}</textarea>
                </x-admin.form-group>

                @include('cms::admin.partials._seo_aside', [
                    'hasFeaturedImage' => $isEdit && !empty($property->thumbnail),
                    'hasMetaImage' => false,
                    'includeShortDescription' => false,
                ])
            </div>

            <div class="d-flex justify-content-between mt-10">
                <button type="button" class="btn btn-light" id="property-prev">{{ __('Back') }}</button>
                <div>
                    <a href="{{ route('admin.properties.index') }}" class="btn btn-light me-3">{{ __('Discard') }}</a>
                    <button type="button" class="btn btn-primary" id="property-next">{{ __('Next') }}</button>
                    <button type="submit" class="btn btn-primary d-none"
                            id="property-submit">{{ __('Save Changes') }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

@section('js')
    @include('base::shared._tinymce')
    <style>
        #property_stepper .property-stepper-nav {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 12px;
            width: 100%;
        }

        #property_stepper .stepper-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            text-align: center;
            min-height: 82px;
        }

        #property_stepper .stepper-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
        }

        #property_stepper .stepper-icon {
            border-radius: 999px;
        }

        #property_stepper .stepper-line {
            display: none;
        }

        @media (max-width: 991px) {
            #property_stepper .property-stepper-nav {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }
    </style>
    <script>
        (async () => {
            const contents = [...document.querySelectorAll('[data-step-content]')];
            const items = [...document.querySelectorAll('.stepper-item')];
            const nextBtn = document.getElementById('property-next');
            const prevBtn = document.getElementById('property-prev');
            const submitBtn = document.getElementById('property-submit');
            const typeSelect = document.getElementById('property_type_id');
            const dynamicContainer = document.getElementById('dynamic-attributes-container');
            let step = 1;

            const renderStep = () => {
                contents.forEach((content) => {
                    content.classList.toggle('d-none', Number(content.dataset.stepContent) !== step);
                });
                items.forEach((item) => {
                    const itemStep = Number(item.dataset.step);
                    item.classList.toggle('current', itemStep === step);
                    item.classList.toggle('completed', itemStep < step);
                });
                prevBtn.disabled = step === 1;
                nextBtn.classList.toggle('d-none', step === 6);
                submitBtn.classList.toggle('d-none', step !== 6);
            };

            nextBtn.addEventListener('click', async () => {
                if (step < 6) step += 1;
                renderStep();
                if (step === 3 && typeSelect?.value) {
                    await loadAttributes();
                }
            });
            prevBtn.addEventListener('click', () => {
                if (step > 1) step -= 1;
                renderStep();
            });

            const dynamicValues = @json(old('dynamic_values', $isEdit ? $property->attributeValues->keyBy('attribute.code')->map(function ($item) {
                return $item->value_text ?? $item->value_number ?? $item->value_boolean;
            })->all() : []));

            const renderAttributeInput = (attribute) => {
                const name = `dynamic_values[${attribute.code}]`;
                const required = attribute.is_required ? 'required' : '';
                const oldValue = dynamicValues[attribute.code] ?? '';
                if (attribute.type === 'boolean') {
                    const normalizedBoolean = oldValue === true ? '1' : (oldValue === false ? '0' : String(oldValue));
                    return `<select class="form-select form-select-solid" name="${name}" ${required}>
                        <option value="">{{ __('Select') }}</option>
                        <option value="1" ${normalizedBoolean === '1' ? 'selected' : ''}>{{ __('Yes') }}</option>
                        <option value="0" ${normalizedBoolean === '0' ? 'selected' : ''}>{{ __('No') }}</option>
                    </select>`;
                }
                if (attribute.type === 'select' || attribute.type === 'multiselect') {
                    const normalizedValues = Array.isArray(oldValue) ? oldValue.map(String) : [String(oldValue)];
                    const options = (attribute.options || [])
                        .map((option) => {
                            const isSelected = normalizedValues.includes(String(option));
                            return `<option value="${option}" ${isSelected ? 'selected' : ''}>${option}</option>`;
                        })
                        .join('');
                    const multiple = attribute.type === 'multiselect' ? 'multiple' : '';
                    const inputName = attribute.type === 'multiselect' ? `${name}[]` : name;
                    return `<select class="form-select form-select-solid" name="${inputName}" ${required} ${multiple}>${options}</select>`;
                }
                if (attribute.type === 'numeric' || attribute.type === 'price') {
                    return `<input type="number" step="0.01" class="form-control form-control-solid" name="${name}" value="${oldValue}" ${required}>`;
                }

                return `<input type="text" class="form-control form-control-solid" name="${name}" value="${oldValue}" ${required}>`;
            };

            let attributesRequestCounter = 0;

            const loadAttributes = async () => {
                const propertyTypeId = typeSelect.value;
                dynamicContainer.innerHTML = '';
                if (!propertyTypeId) return;

                const requestId = ++attributesRequestCounter;

                try {
                    const response = await fetch(`{{ route('admin.properties.attributes') }}?property_type_id=${propertyTypeId}`);
                    if (!response.ok) {
                        throw new Error('Failed to load attributes');
                    }

                    const data = await response.json();
                    if (requestId !== attributesRequestCounter) {
                        return;
                    }

                    if (!data.attributes.length) {
                        dynamicContainer.innerHTML = `<div class="alert alert-light-info">{{ __('No dynamic attributes in selected family.') }}</div>`;
                        return;
                    }

                    data.attributes.forEach((attribute) => {
                        const html = `
                            <div class="mb-6">
                                <label class="form-label fw-semibold">${attribute.name}</label>
                                ${renderAttributeInput(attribute)}
                            </div>
                        `;
                        dynamicContainer.insertAdjacentHTML('beforeend', html);
                    });
                } catch (error) {
                    dynamicContainer.innerHTML = `<div class="alert alert-light-danger">{{ __('No dynamic attributes in selected family.') }}</div>`;
                }
            };

            typeSelect.addEventListener('change', loadAttributes);
            renderStep();
            loadAttributes();

            const dropzone = document.getElementById('property-slides-dropzone');
            const slidesInput = document.getElementById('slides-input');
            const slidesCountText = document.getElementById('slides-count-text');
            dropzone?.addEventListener('click', () => slidesInput.click());
            slidesInput?.addEventListener('change', () => {
                const count = slidesInput.files?.length ?? 0;
                if (slidesCountText) {
                    slidesCountText.textContent = count > 0
                        ? `{{ __('Selected images') }}: ${count}`
                        : `{{ __('No files selected.') }}`;
                }
            });

            if (window.jQuery && window.jQuery.fn.select2) {
                window.jQuery('#location_id, #property_type_id').select2();
            }
        })();
    </script>
@endsection
