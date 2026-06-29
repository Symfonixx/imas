@php
    $isEdit = isset($property);
    $seoMeta = $isEdit ? ($property->metadata ?? []) : [];
    $defaultStatus = old('status', $isEdit ? (($property->status?->value) ?? 'Published') : 'Published');
    $prefillCityId = null;
    $prefillDistrictId = old('district_id');
    $selectedAreaIdValue = old('area_id');

    if ($prefillDistrictId === null && $selectedAreaIdValue === null && $isEdit && $property->location_id) {
        $prefillLoc = \Modules\Property\Models\Location::query()
            ->with(['parent:id,parent_id,type', 'parent.parent:id,parent_id,type'])
            ->find((int) $property->location_id);

        if ($prefillLoc) {
            if ($prefillLoc->type === \Modules\Property\Enums\LocationType::Area) {
                $selectedAreaIdValue = $prefillLoc->id;
                $prefillDistrictId = $prefillLoc->parent_id;
                $par = $prefillLoc->parent;
                if ($par && $par->type === \Modules\Property\Enums\LocationType::Municipality) {
                    $prefillCityId = $par->parent_id;
                } elseif ($par && $par->type === \Modules\Property\Enums\LocationType::City) {
                    $prefillCityId = $par->id;
                    $prefillDistrictId = null;
                }
            } elseif ($prefillLoc->type === \Modules\Property\Enums\LocationType::Municipality) {
                $prefillDistrictId = $prefillLoc->id;
                $prefillCityId = $prefillLoc->parent_id;
            }
        }
    } elseif ($prefillDistrictId !== null || $selectedAreaIdValue !== null) {
        $resolveId = $selectedAreaIdValue ?: $prefillDistrictId;
        if ($resolveId) {
            $prefillLoc = \Modules\Property\Models\Location::query()
                ->with(['parent:id,parent_id,type', 'parent.parent:id,parent_id,type'])
                ->find((int) $resolveId);

            if ($prefillLoc) {
                if ($prefillLoc->type === \Modules\Property\Enums\LocationType::Area && $prefillLoc->parent) {
                    if ($prefillDistrictId === null) {
                        $prefillDistrictId = $prefillLoc->parent_id;
                    }
                    $par = $prefillLoc->parent;
                    if ($par->type === \Modules\Property\Enums\LocationType::Municipality) {
                        $prefillCityId = $par->parent_id;
                    }
                } elseif ($prefillLoc->type === \Modules\Property\Enums\LocationType::Municipality) {
                    $prefillCityId = $prefillLoc->parent_id;
                }
            }
        }
    }

    $rawOldUt = old('unit_types');
    if (is_array($rawOldUt) && $rawOldUt !== []) {
        $unitTypeRowsForView = array_values($rawOldUt);
    } elseif ($isEdit && $property->unitTypes->isNotEmpty()) {
        $unitTypeRowsForView = $property->unitTypes->map(fn ($u) => [
            'id' => $u->id,
            'catalog_id' => $u->catalog_id,
            'name' => $u->name,
            'min_area' => $u->min_area,
            'max_area' => $u->max_area,
            'price' => $u->price,
        ])->all();
    } else {
        $unitTypeRowsForView = [];
    }

    $catalogItems = $projectUnitTypesCatalog ?? [];

    $selectedSimilarPropertyIds = collect(old('similar_property_ids'))
        ->map(fn ($id) => (int) $id)
        ->filter(fn ($id) => $id > 0)
        ->values()
        ->all();

    if ($selectedSimilarPropertyIds === [] && $isEdit) {
        $selectedSimilarPropertyIds = $property->similarProperties->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    $currentPropertyId = $isEdit ? (int) $property->id : null;
@endphp

<div class="card card-flush mb-7">
    <div class="card-body">
        <div class="row mb-2">
            <div class="col-xl-12">
                <x-admin.form-group label="Thumbnail" name="thumbnail" :required="! $isEdit"
                                    helper="Recommended dimensions: 1200px × 900px (4:3).">
                    <x-admin.image-input name="thumbnail"
                                         :preview="$isEdit && $property->thumbnail ? asset('storage/' . $property->thumbnail) : null"/>
                </x-admin.form-group>
            </div>

        </div>
        <div class="row">

            <div class="col-xl-6">
                <x-admin.form-group label="Project type" name="property_type_id" required>
                    <select name="property_type_id" id="property_type_id" class="form-select form-select-solid"
                            data-control="select2" data-placeholder="{{ __('Select property type') }}">
                        <option value="">{{ __('Select property type') }}</option>
                        @foreach($propertyTypes as $propertyType)
                            <option
                                value="{{ $propertyType['id'] }}" @selected((string) old('property_type_id', optional($property)->property_type_id ?? '') === (string) $propertyType['id'])>
                                {{ $propertyType['name'] }}
                            </option>
                        @endforeach
                    </select>
                </x-admin.form-group>
            </div>

            <div class="col-xl-6">

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
            </div>

            <div class="col-xl-6">
                <x-admin.form-group label="Project code" name="project_code" required>
                    <input id="slug" type="text" name="project_code" class="form-control form-control-solid"
                           value="{{ old('project_code', optional($property)->project_code) }}"/>
                </x-admin.form-group>
            </div>

            <div class="col-xl-6">
                <x-admin.form-group label="Project name" name="project_name" translatable>
                    <input id="project_name" type="text" name="project_name" class="form-control form-control-solid"
                           value="{{ old('project_name', optional($property)->project_name) }}"/>
                </x-admin.form-group>
            </div>


            <div class="col-12">
                <x-admin.form-group label="Project title" name="title" required translatable>
                    <input id="title" type="text" name="title" class="form-control form-control-solid"
                           value="{{ old('title', optional($property)->title) }}"/>
                </x-admin.form-group>


                <x-admin.form-group label="Overview" name="overview" required translatable>
                    <textarea id="tinymce-overview"
                              class="form-control form-control-solid tinymce-editor"
                              name="overview"
                              rows="8">{!! old('overview', optional($property)->overview) !!}</textarea>
                </x-admin.form-group>
            </div>
        </div>


        <div class="row mb-2">
            <div class="col-md-4">
                <x-admin.form-group label="City" name="city_id">
                    <select id="city_id" class="form-select form-select-solid">
                        <option value="">{{ __('Select city') }}</option>
                        @foreach(($cities ?? []) as $city)
                            <option
                                value="{{ $city['id'] }}" @selected((string) ($prefillCityId ?? '') === (string) $city['id'])>{{ $city['name'] }}</option>
                        @endforeach
                    </select>
                </x-admin.form-group>
            </div>
            <div class="col-md-4">
                <x-admin.form-group label="Municipality" name="district_id" required>
                    <select id="district_id" name="district_id" class="form-select form-select-solid" required
                        @disabled(! $prefillCityId && ! old('district_id'))>
                        <option value="">{{ __('Select municipality') }}</option>
                    </select>
                </x-admin.form-group>
            </div>
            <div class="col-md-4">
                <x-admin.form-group label="Location" name="area_id">
                    <select id="area_id" name="area_id" class="form-select form-select-solid"
                        @disabled(! $prefillDistrictId && ! old('district_id'))>
                        <option value="">{{ __('Select area') }}</option>
                    </select>
                </x-admin.form-group>
            </div>
        </div>


        <div class="fv-row mb-7">
            <label class="form-label fw-semibold fs-6">{{ __('Unit types') }}</label>
            <div
                class="text-muted fs-7 mb-3">{{ __('Add one row per layout. Choose a type, then enter area and starting price.') }}</div>
            <div id="unit-type-rows">
                @foreach($unitTypeRowsForView as $i => $ut)
                    @php
                        $nameVal = (string) old("unit_types.$i.name", $ut['name'] ?? '');
                        $cid = old("unit_types.$i.catalog_id", $ut['catalog_id'] ?? '');
                        $catalogIds = collect($catalogItems)->pluck('id')->map(fn ($v) => (string) $v)->all();
                        $inCatalog = $cid !== null && $cid !== '' && in_array((string) $cid, $catalogIds, true);
                    @endphp
                    <div class="card card-bordered mb-4 js-unit-row" data-row-index="{{ $i }}">
                        <div class="card-body py-4">
                            <div class="row g-3 align-items-end">
                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label">{{ __('Unit type') }}</label>
                                    <select class="form-select form-select-solid js-unit-preset">
                                        <option value="">{{ __('Select') }}</option>
                                        @foreach($catalogItems as $c)
                                            <option value="{{ $c['id'] }}" data-label="{{ e($c['name']) }}"
                                                @selected((string) $cid === (string) $c['id'])>{{ $c['name'] }}</option>
                                        @endforeach
                                        <option
                                            value="__other__" @selected($nameVal !== '' && ! $inCatalog)>{{ __('Other') }}</option>
                                    </select>
                                    <input type="text"
                                           class="form-control form-control-solid mt-2 js-unit-custom {{ ($nameVal !== '' && ! $inCatalog) ? '' : 'd-none' }}"
                                           value="{{ ($nameVal !== '' && ! $inCatalog) ? $nameVal : '' }}"
                                           placeholder="{{ __('Custom unit type') }}"
                                           autocomplete="off">
                                </div>
                                <div class="col-lg-8">
                                    <div
                                        class="row g-3 js-unit-numeric {{ ($nameVal !== '' || $inCatalog) ? '' : 'd-none' }}">
                                        <div class="col-md-4">
                                            <label class="form-label">{{ __('Min area') }}</label>
                                            <input type="number" step="0.01" min="0"
                                                   class="form-control form-control-solid"
                                                   name="unit_types[{{ $i }}][min_area]"
                                                   value="{{ old("unit_types.$i.min_area", $ut['min_area'] ?? '') }}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">{{ __('Max area') }}</label>
                                            <input type="number" step="0.01" min="0"
                                                   class="form-control form-control-solid"
                                                   name="unit_types[{{ $i }}][max_area]"
                                                   value="{{ old("unit_types.$i.max_area", $ut['max_area'] ?? '') }}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">{{ __('Starting price') }}</label>
                                            <input type="number" step="0.01" min="0"
                                                   class="form-control form-control-solid"
                                                   name="unit_types[{{ $i }}][price]"
                                                   value="{{ old("unit_types.$i.price", $ut['price'] ?? '') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-1 col-md-6 text-lg-end">
                                    <button type="button" class="btn btn-sm btn-light-danger js-remove-unit-row"
                                            title="{{ __('Remove') }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <input type="hidden" name="unit_types[{{ $i }}][id]" class="js-unit-id"
                                   value="{{ $ut['id'] ?? '' }}">
                            <input type="hidden" name="unit_types[{{ $i }}][catalog_id]" class="js-unit-catalog-id"
                                   value="{{ $inCatalog ? $cid : '' }}">
                            <input type="hidden" name="unit_types[{{ $i }}][name]" class="js-unit-name-hidden"
                                   value="{{ $nameVal }}">
                        </div>
                    </div>
                @endforeach
            </div>
            <button type="button" class="btn btn-sm btn-light-primary" id="add-unit-type-row">
                <i class="bi bi-plus-lg"></i> {{ __('Add unit type') }}
            </button>
        </div>

        <x-admin.form-group label="Why to buy this property" name="why_to_buy" required translatable>
            <textarea class="form-control form-control-solid" name="why_to_buy"
                      rows="6">{{ old('why_to_buy', optional($property)->why_to_buy) }}</textarea>
        </x-admin.form-group>

        <x-admin.form-group label="Facilities" name="facilities" translatable>
            <textarea id="tinymce-facilities"
                      class="form-control form-control-solid tinymce-editor"
                      name="facilities"
                      rows="6">{!! old('facilities', optional($property)->facilities) !!}</textarea>
        </x-admin.form-group>

        <x-admin.form-group label="Location specifications" name="content" required translatable>
            <textarea id="tinymce-content"
                      class="form-control form-control-solid tinymce-editor"
                      name="content"
                      rows="8">{!! old('content', optional($property)->content) !!}</textarea>
        </x-admin.form-group>

        <div class="row mb-2">
            <div class="col-md-6">
                <x-admin.form-group label="Latitude" name="lat">
                    <input type="text" inputmode="decimal" name="lat" class="form-control form-control-solid"
                           value="{{ old('lat', optional($property)->lat) }}"/>
                </x-admin.form-group>
            </div>
            <div class="col-md-6">
                <x-admin.form-group label="Longitude" name="lng">
                    <input type="text" inputmode="decimal" name="lng" class="form-control form-control-solid"
                           value="{{ old('lng', optional($property)->lng) }}"/>
                </x-admin.form-group>
            </div>
        </div>

        <div class="card card-bordered mb-7">
            <div class="card-body py-4">
                <x-admin.toggle-switch name="is_sold_out" label="Sold out"
                                       :checked="(bool) old('is_sold_out', optional($property)->is_sold_out ?? false)"/>
                <x-admin.toggle-switch name="is_recommended" label="Recommended"
                                       :checked="(bool) old('is_recommended', optional($property)->is_recommended ?? false)"/>
                <x-admin.toggle-switch name="is_citizenship_eligible" label="Suitable for citizenship"
                                       :checked="(bool) old('is_citizenship_eligible', optional($property)->is_citizenship_eligible ?? false)"/>
                <x-admin.toggle-switch name="is_featured" label="Featured"
                                       :checked="(bool) old('is_featured', optional($property)->is_featured ?? false)"
                                       last/>
            </div>
        </div>

        <x-admin.form-group label="Youtube link" name="youtube_video_url">
            <input type="url" name="youtube_video_url" class="form-control form-control-solid"
                   value="{{ old('youtube_video_url', optional($property)->youtube_video_url) }}"/>
        </x-admin.form-group>

        <x-admin.form-group label="Similar properties" name="similar_property_ids"
                            helper="Choose up to 12 related projects to show on the property page. Leave empty to show properties of the same type automatically.">
            <select name="similar_property_ids[]" id="similar_property_ids"
                    class="form-select form-select-solid"
                    data-control="select2"
                    data-placeholder="{{ __('Select similar properties') }}"
                    data-allow-clear="true"
                    multiple="multiple">
                @foreach(($propertiesForSimilar ?? []) as $similarOption)
                    @if($currentPropertyId === null || (int) $similarOption['id'] !== $currentPropertyId)
                        <option value="{{ $similarOption['id'] }}"
                            @selected(in_array((int) $similarOption['id'], $selectedSimilarPropertyIds, true))>
                            {{ $similarOption['label'] }}
                        </option>
                    @endif
                @endforeach
            </select>
        </x-admin.form-group>

        <x-admin.form-group label="Add photos" name="slides"
                            helper="Recommended dimensions: 1920px × 1080px. Maximum 20 images.">
            <div class="dropzone border-dashed border-primary rounded p-5 text-center" id="property-slides-dropzone">
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
                            <img src="{{ asset('storage/'.$slide->image) }}" class="img-fluid rounded border"
                                 alt="slide">
                        </div>
                    @endforeach
                </div>
            @endif
        </x-admin.form-group>

        <div class="separator separator-dashed my-10"></div>
        <h3 class="fw-bold mb-5">{{ __('SEO settings') }}</h3>

        @include('cms::admin.partials._seo_section', [
            'metaTitle' => old('meta_title', $seoMeta['meta_title'] ?? ''),
            'metaDescription' => old('meta_description', $seoMeta['meta_description'] ?? ''),
            'metaKeywords' => old('meta_keywords', implode(', ', $seoMeta['meta_keywords'] ?? [])),
            'metaImagePreview' => null,
            'titleSource' => '#title',
            'descSource' => '#meta_description',
            'slugSource' => '#slug',
            'baseUrl' => url('/').'/properties/',
        ])

        <x-admin.form-group label="Schema JSON-LD" name="meta_schema">
            <textarea class="form-control form-control-solid" rows="5"
                      name="meta_schema">{{ old('meta_schema', $seoMeta['schema'] ?? '') }}</textarea>
        </x-admin.form-group>

        @include('cms::admin.partials._seo_aside', [
            'hasFeaturedImage' => $isEdit && ! empty($property->thumbnail),
            'hasMetaImage' => false,
            'includeShortDescription' => false,
        ])

        <div class="d-flex justify-content-end gap-3 mt-10">
            <a href="{{ route('admin.properties.index') }}" class="btn btn-light">{{ __('Discard') }}</a>
            <button type="submit" class="btn btn-primary"
                    id="property-submit">{{ $isEdit ? __('Save Changes') : __('Save') }}</button>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        (function () {
            const LOCATION_CHILDREN_URL = @json(route('admin.properties.location_children'));
            const TYPE_DISTRICT = 'district';
            const TYPE_AREA = 'area';
            const IS_EDIT = @json($isEdit);
            const PREFILL_CITY_ID = @json($prefillCityId);
            const PREFILL_DISTRICT_ID = @json($prefillDistrictId);
            const PREFILL_AREA_ID = @json($selectedAreaIdValue);
            const CATALOG_ITEMS = @json($catalogItems);
            const OTHER_VALUE = '__other__';

            const $city = window.jQuery ? window.jQuery('#city_id') : null;
            const $district = window.jQuery ? window.jQuery('#district_id') : null;
            const $area = window.jQuery ? window.jQuery('#area_id') : null;

            async function fetchLocations(parentId, type) {
                const params = new URLSearchParams();
                if (parentId !== null && parentId !== undefined && parentId !== '') {
                    params.set('parent_id', String(parentId));
                }
                params.set('type', type);
                const res = await fetch(`${LOCATION_CHILDREN_URL}?${params.toString()}`, {
                    headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
                });
                if (!res.ok) {
                    return [];
                }
                const data = await res.json();

                return Array.isArray(data.items) ? data.items : [];
            }

            function fillSelect($select, items, selectedId, placeholder) {
                if (!$select || !$select.length) {
                    return;
                }
                const sel = $select[0];
                const current = selectedId !== null && selectedId !== undefined && selectedId !== ''
                    ? String(selectedId)
                    : '';
                sel.innerHTML = '';
                const opt0 = document.createElement('option');
                opt0.value = '';
                opt0.textContent = placeholder;
                sel.appendChild(opt0);
                items.forEach((item) => {
                    const o = document.createElement('option');
                    o.value = String(item.id);
                    o.textContent = item.name;
                    if (current !== '' && String(item.id) === current) {
                        o.selected = true;
                    }
                    sel.appendChild(o);
                });
                $select.prop('disabled', items.length === 0);
                if (current) {
                    $select.val(current);
                }
                $select.trigger('change');
            }

            async function onCityChange() {
                const cityId = $city?.val();
                fillSelect($district, [], '', @json(__('Select municipality')));
                fillSelect($area, [], '', @json(__('Select area')));
                if (!cityId) {
                    $district?.prop('disabled', true);
                    $area?.prop('disabled', true);

                    return;
                }
                const districts = await fetchLocations(cityId, TYPE_DISTRICT);
                fillSelect($district, districts, '', @json(__('Select municipality')));
                $district?.prop('disabled', districts.length === 0);
                $area?.prop('disabled', true);
            }

            async function onDistrictChange() {
                const districtId = $district?.val();
                fillSelect($area, [], '', @json(__('Select area')));
                if (!districtId) {
                    $area?.prop('disabled', true);

                    return;
                }
                const areas = await fetchLocations(districtId, TYPE_AREA);
                fillSelect($area, areas, '', @json(__('Select area')));
                $area?.prop('disabled', areas.length === 0);
            }

            function initLocationCascade() {
                if (!$city?.length) {
                    return;
                }
                $city.off('change.propertyLoc');
                $district.off('change.propertyLoc');
                (async () => {
                    if (!PREFILL_CITY_ID) {
                        return;
                    }
                    $city.val(String(PREFILL_CITY_ID)).trigger('change');
                    const districts = await fetchLocations(PREFILL_CITY_ID, TYPE_DISTRICT);
                    fillSelect($district, districts, PREFILL_DISTRICT_ID, @json(__('Select municipality')));
                    $district?.prop('disabled', districts.length === 0);
                    if (PREFILL_DISTRICT_ID) {
                        const areas = await fetchLocations(PREFILL_DISTRICT_ID, TYPE_AREA);
                        fillSelect($area, areas, PREFILL_AREA_ID, @json(__('Select area')));
                        $area?.prop('disabled', areas.length === 0);
                    } else {
                        fillSelect($area, [], '', @json(__('Select area')));
                        $area?.prop('disabled', true);
                    }
                })().finally(() => {
                    $city.on('change.propertyLoc', onCityChange);
                    $district.on('change.propertyLoc', onDistrictChange);
                });
            }

            const unitRowsEl = document.getElementById('unit-type-rows');
            const addUnitBtn = document.getElementById('add-unit-type-row');
            let unitRowIndex = unitRowsEl ? unitRowsEl.querySelectorAll('.js-unit-row').length : 0;

            function syncUnitRowHidden(row) {
                const preset = row.querySelector('.js-unit-preset');
                const custom = row.querySelector('.js-unit-custom');
                const hidden = row.querySelector('.js-unit-name-hidden');
                const catalogHidden = row.querySelector('.js-unit-catalog-id');
                const numeric = row.querySelector('.js-unit-numeric');
                if (!preset || !hidden) {
                    return;
                }
                let name = '';
                const pv = preset.value;
                if (pv === OTHER_VALUE) {
                    if (catalogHidden) {
                        catalogHidden.value = '';
                    }
                    name = (custom && custom.value) ? custom.value.trim() : '';
                    custom?.classList.remove('d-none');
                } else if (pv) {
                    if (catalogHidden) {
                        catalogHidden.value = pv;
                    }
                    const opt = preset.selectedOptions[0];
                    name = (opt && opt.dataset && opt.dataset.label)
                        ? String(opt.dataset.label).trim()
                        : (opt ? String(opt.textContent || '').trim() : '');
                    custom?.classList.add('d-none');
                } else {
                    if (catalogHidden) {
                        catalogHidden.value = '';
                    }
                    custom?.classList.add('d-none');
                }
                hidden.value = name;
                if (numeric) {
                    const hasCatalog = catalogHidden && String(catalogHidden.value).trim() !== '';
                    numeric.classList.toggle('d-none', name === '' && !hasCatalog);
                }
            }

            function bindUnitRow(row) {
                const preset = row.querySelector('.js-unit-preset');
                const custom = row.querySelector('.js-unit-custom');
                preset?.addEventListener('change', () => syncUnitRowHidden(row));
                custom?.addEventListener('input', () => syncUnitRowHidden(row));
                row.querySelector('.js-remove-unit-row')?.addEventListener('click', () => {
                    row.remove();
                    reindexUnitRows();
                });
                syncUnitRowHidden(row);
            }

            function reindexUnitRows() {
                if (!unitRowsEl) {
                    return;
                }
                [...unitRowsEl.querySelectorAll('.js-unit-row')].forEach((row, i) => {
                    row.dataset.rowIndex = String(i);
                    row.querySelectorAll('[name^="unit_types["]').forEach((el) => {
                        const n = el.getAttribute('name');
                        if (!n) {
                            return;
                        }
                        el.setAttribute('name', n.replace(/unit_types\[\d+]/, `unit_types[${i}]`));
                    });
                });
                unitRowIndex = unitRowsEl.querySelectorAll('.js-unit-row').length;
            }

            function addUnitRow() {
                if (!unitRowsEl) {
                    return;
                }
                const i = unitRowIndex++;
                const wrap = document.createElement('div');
                wrap.className = 'card card-bordered mb-4 js-unit-row';
                wrap.dataset.rowIndex = String(i);
                const body = document.createElement('div');
                body.className = 'card-body py-4';
                const row = document.createElement('div');
                row.className = 'row g-3 align-items-end';
                const colPreset = document.createElement('div');
                colPreset.className = 'col-lg-3 col-md-6';
                const lblPreset = document.createElement('label');
                lblPreset.className = 'form-label';
                lblPreset.textContent = @json(__('Unit type'));
                const sel = document.createElement('select');
                sel.className = 'form-select form-select-solid js-unit-preset';
                const opt0 = document.createElement('option');
                opt0.value = '';
                opt0.textContent = @json(__('Select'));
                sel.appendChild(opt0);
                CATALOG_ITEMS.forEach((item) => {
                    const o = document.createElement('option');
                    o.value = String(item.id);
                    o.textContent = item.name;
                    o.dataset.label = item.name;
                    sel.appendChild(o);
                });
                const optOther = document.createElement('option');
                optOther.value = OTHER_VALUE;
                optOther.textContent = @json(__('Other'));
                sel.appendChild(optOther);
                const custom = document.createElement('input');
                custom.type = 'text';
                custom.className = 'form-control form-control-solid mt-2 d-none js-unit-custom';
                custom.placeholder = @json(__('Custom unit type'));
                custom.autocomplete = 'off';
                colPreset.appendChild(lblPreset);
                colPreset.appendChild(sel);
                colPreset.appendChild(custom);
                const colNum = document.createElement('div');
                colNum.className = 'col-lg-8';
                const numWrap = document.createElement('div');
                numWrap.className = 'row g-3 js-unit-numeric d-none';
                [['min_area', @json(__('Min area'))], ['max_area', @json(__('Max area'))], ['price', @json(__('Starting price'))]].forEach(([field, labelText]) => {
                    const c = document.createElement('div');
                    c.className = 'col-md-4';
                    const l = document.createElement('label');
                    l.className = 'form-label';
                    l.textContent = labelText;
                    const inp = document.createElement('input');
                    inp.type = 'number';
                    inp.step = '0.01';
                    inp.min = '0';
                    inp.className = 'form-control form-control-solid';
                    inp.name = `unit_types[${i}][${field}]`;
                    c.appendChild(l);
                    c.appendChild(inp);
                    numWrap.appendChild(c);
                });
                colNum.appendChild(numWrap);
                const colRm = document.createElement('div');
                colRm.className = 'col-lg-1 col-md-6 text-lg-end';
                const btnRm = document.createElement('button');
                btnRm.type = 'button';
                btnRm.className = 'btn btn-sm btn-light-danger js-remove-unit-row';
                btnRm.title = @json(__('Remove'));
                btnRm.innerHTML = '<i class="bi bi-trash"></i>';
                colRm.appendChild(btnRm);
                row.appendChild(colPreset);
                row.appendChild(colNum);
                row.appendChild(colRm);
                const hidId = document.createElement('input');
                hidId.type = 'hidden';
                hidId.name = `unit_types[${i}][id]`;
                hidId.className = 'js-unit-id';
                hidId.value = '';
                const hidCatalog = document.createElement('input');
                hidCatalog.type = 'hidden';
                hidCatalog.name = `unit_types[${i}][catalog_id]`;
                hidCatalog.className = 'js-unit-catalog-id';
                hidCatalog.value = '';
                const hidName = document.createElement('input');
                hidName.type = 'hidden';
                hidName.name = `unit_types[${i}][name]`;
                hidName.className = 'js-unit-name-hidden';
                hidName.value = '';
                body.appendChild(row);
                body.appendChild(hidId);
                body.appendChild(hidCatalog);
                body.appendChild(hidName);
                wrap.appendChild(body);
                unitRowsEl.appendChild(wrap);
                bindUnitRow(wrap);
            }

            document.querySelectorAll('.js-unit-row').forEach(bindUnitRow);
            addUnitBtn?.addEventListener('click', () => addUnitRow());

            const propertyForm = document.getElementById('property-form');
            propertyForm?.addEventListener('submit', () => {
                if (typeof tinymce !== 'undefined') {
                    tinymce.triggerSave();
                }

                document.querySelectorAll('.js-unit-row').forEach((row) => syncUnitRowHidden(row));
                document.querySelectorAll('.js-unit-row').forEach((row) => {
                    const h = row.querySelector('.js-unit-name-hidden');
                    const c = row.querySelector('.js-unit-catalog-id');
                    const keep = (h && String(h.value).trim() !== '') || (c && String(c.value).trim() !== '');
                    if (!keep) {
                        row.remove();
                    }
                });
                reindexUnitRows();
                propertyForm.querySelectorAll('input[name="unit_types_sync_empty"]').forEach((el) => el.remove());
                if (!IS_EDIT || !unitRowsEl) {
                    return;
                }
                const hasUnitField = propertyForm.querySelector('[name^="unit_types["]');
                if (!hasUnitField) {
                    const m = document.createElement('input');
                    m.type = 'hidden';
                    m.name = 'unit_types_sync_empty';
                    m.value = '1';
                    propertyForm.appendChild(m);
                }
            });

            if (window.jQuery && window.jQuery.fn.select2) {
                window.jQuery('#property_type_id, select[name="status"], #similar_property_ids').select2();
            }
            initLocationCascade();

            const dropzone = document.getElementById('property-slides-dropzone');
            const slidesInput = document.getElementById('slides-input');
            const slidesCountText = document.getElementById('slides-count-text');
            dropzone?.addEventListener('click', () => slidesInput?.click());
            slidesInput?.addEventListener('change', () => {
                const count = slidesInput.files?.length ?? 0;
                if (slidesCountText) {
                    slidesCountText.textContent = count > 0
                        ? `{{ __('Selected images') }}: ${count}`
                        : `{{ __('No files selected.') }}`;
                }
            });
        })();
    </script>
@endpush
