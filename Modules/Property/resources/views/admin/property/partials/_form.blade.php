@php
    $isEdit = isset($property);
    $seoMeta = $isEdit ? ($property->metadata ?? []) : [];
    $defaultStatus = old('status', $isEdit ? (($property->status?->value) ?? 'Published') : 'Published');
    $prefillCityId = $prefillCityId ?? null;
    $prefillDistrictId = $prefillDistrictId ?? old('district_id');
    $selectedAreaIdValue = $selectedAreaIdValue ?? old('area_id');

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

    $selectedSlideCategoryIds = collect(old('slide_category_ids'))
        ->map(fn ($id) => (int) $id)
        ->filter(fn ($id) => $id > 0)
        ->values()
        ->all();

    $slideCategorySelectionWasSubmitted = session()->hasOldInput('slide_category_ids_present');

    if (! $slideCategorySelectionWasSubmitted && $selectedSlideCategoryIds === [] && $isEdit) {
        $selectedSlideCategoryIds = $property->slideCategories->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    $removedSlideMediaIds = collect(old('remove_slide_media_ids', []))
        ->map(fn ($id) => (int) $id)
        ->filter(fn ($id) => $id > 0)
        ->values()
        ->all();

    $existingSlideMediaByCategory = $isEdit
        ? $property->slideMedia
            ->reject(fn ($media) => in_array((int) $media->id, $removedSlideMediaIds, true))
            ->groupBy('slide_category_id')
        : collect();

    $selectedAttributeGroupIds = collect(old('attribute_group_ids'))
        ->map(fn ($id) => (int) $id)
        ->filter(fn ($id) => $id > 0)
        ->values()
        ->all();

    $attributeGroupSelectionWasSubmitted = session()->hasOldInput('attribute_group_ids_present');

    if (! $attributeGroupSelectionWasSubmitted && $selectedAttributeGroupIds === [] && $isEdit) {
        $selectedAttributeGroupIds = $property->attributeGroups->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    $currentPropertyId = $isEdit ? (int) $property->id : null;
@endphp

<div class="row gx-5 gx-xl-10">
    <div class="col-xxl-8 col-xl-8 mb-5 mb-xl-0">
        {{-- Identity --}}
        <div class="card card-flush mb-7">
            <div class="card-header">
                <div class="card-title">
                    <h2 class="d-flex align-items-center">
                        <i class="bi bi-building text-primary fs-3 me-2"></i>
                        {{ __('Identity') }}
                    </h2>
                </div>
            </div>
            <div class="card-body pt-3">
                <x-admin.form-group label="Thumbnail" name="thumbnail"
                                    helper="Recommended dimensions: 1200px × 900px (4:3).">
                    <x-admin.image-input name="thumbnail"
                                         :preview="$isEdit && $property->thumbnail ? asset('storage/' . $property->thumbnail) : null"
                                         :mediaPath="$isEdit ? ($property->thumbnail ?? null) : null"/>
                </x-admin.form-group>

                <div class="row g-5">
                    <div class="col-md-6">
                        <x-admin.form-group label="Project title" name="title" required translatable>
                            <input id="title" type="text" name="title" class="form-control form-control-solid"
                                   value="{{ old('title', optional($property)->title) }}"/>
                        </x-admin.form-group>
                    </div>
                    <div class="col-md-6">
                        <x-admin.form-group label="Project name" name="project_name" required translatable>
                            <input id="project_name" type="text" name="project_name" class="form-control form-control-solid"
                                   value="{{ old('project_name', optional($property)->project_name) }}"/>
                        </x-admin.form-group>
                    </div>
                    <div class="col-md-6">
                        <x-admin.form-group label="Project code" name="project_code" required
                                            helper="{{ __('Internal inventory code. Unique per property.') }}">
                            <input type="text" name="project_code" class="form-control form-control-solid"
                                   value="{{ old('project_code', optional($property)->project_code) }}"/>
                        </x-admin.form-group>
                    </div>
                    <div class="col-md-6">
                        <x-admin.form-group label="URL key" name="url_key" required
                                            helper="{{ __('Lowercase, hyphens only. Used in the property URL.') }}">
                            <input id="url_key" type="text" name="url_key" class="form-control form-control-solid"
                                   value="{{ old('url_key', optional($property)->url_key) }}"
                                   pattern="[a-z0-9]+(-[a-z0-9]+)*"
                                   maxlength="191"
                                   placeholder="my-property-slug"/>
                        </x-admin.form-group>
                    </div>
                    <div class="col-md-6">
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
                    <div class="col-md-6">
                        <x-admin.form-group label="Status" name="status">
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
                </div>
            </div>
        </div>

        {{-- Location --}}
        <div class="card card-flush mb-7">
            <div class="card-header">
                <div class="card-title">
                    <h2 class="d-flex align-items-center">
                        <i class="bi bi-geo-alt text-primary fs-3 me-2"></i>
                        {{ __('Location') }}
                    </h2>
                </div>
            </div>
            <div class="card-body pt-3">
                <div class="row g-5">
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
                        <x-admin.form-group label="Municipality" name="district_id">
                            <select id="district_id" name="district_id" class="form-select form-select-solid"
                                @disabled(! $prefillCityId && ! old('district_id'))>
                                <option value="">{{ __('Select municipality') }}</option>
                            </select>
                        </x-admin.form-group>
                    </div>
                    <div class="col-md-4">
                        <x-admin.form-group label="Location" name="area_id">
                            <select id="area_id" name="area_id" class="form-select form-select-solid"
                                @disabled(! $prefillCityId && ! old('area_id') && ! old('district_id'))>
                                <option value="">{{ __('Select area') }}</option>
                            </select>
                        </x-admin.form-group>
                    </div>
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
            </div>
        </div>

        {{-- Content --}}
        <div class="card card-flush mb-7">
            <div class="card-header">
                <div class="card-title">
                    <h2 class="d-flex align-items-center">
                        <i class="bi bi-file-text text-primary fs-3 me-2"></i>
                        {{ __('Content') }}
                    </h2>
                </div>
            </div>
            <div class="card-body pt-3">
                <x-admin.form-group label="Overview" name="overview" translatable>
                    <textarea id="tinymce-overview"
                              class="form-control form-control-solid tinymce-editor"
                              name="overview"
                              rows="8">{!! old('overview', optional($property)->overview) !!}</textarea>
                </x-admin.form-group>

                <x-admin.form-group label="Why to buy this property" name="why_to_buy" translatable>
                    <textarea id="tinymce-why-to-buy"
                              class="form-control form-control-solid tinymce-editor"
                              name="why_to_buy"
                              rows="6">{!! old('why_to_buy', optional($property)->why_to_buy) !!}</textarea>
                </x-admin.form-group>

                <x-admin.form-group label="Content" name="content" translatable
                                    helper="{{ __('Location specifications and additional project details.') }}">
                    <textarea id="tinymce-content"
                              class="form-control form-control-solid tinymce-editor"
                              name="content"
                              rows="8">{!! old('content', optional($property)->content) !!}</textarea>
                </x-admin.form-group>
            </div>
        </div>

        {{-- Unit types --}}
        <div class="card card-flush mb-7">
            <div class="card-header">
                <div class="card-title">
                    <h2 class="d-flex align-items-center">
                        <i class="bi bi-grid-3x3-gap text-primary fs-3 me-2"></i>
                        {{ __('Unit types') }}
                    </h2>
                </div>
            </div>
            <div class="card-body pt-3">
                <div class="text-muted fs-7 mb-5">{{ __('Add one row per layout. Choose a type, then enter area and starting price.') }}</div>
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
        </div>

        {{-- Attributes --}}
        <div class="card card-flush mb-7">
            <div class="card-header">
                <div class="card-title">
                    <h2 class="d-flex align-items-center">
                        <i class="bi bi-sliders text-primary fs-3 me-2"></i>
                        {{ __('Property attributes') }}
                    </h2>
                </div>
            </div>
            <div class="card-body pt-3">
                <x-admin.form-group label="Attribute groups" name="attribute_group_ids"
                                    helper="{{ __('Select one or more groups to load their related attribute fields.') }}">
                    <input type="hidden" name="attribute_group_ids_present" value="1"/>
                    <select name="attribute_group_ids[]" id="attribute_group_ids"
                            class="form-select form-select-solid"
                            data-control="select2"
                            data-placeholder="{{ __('Select attribute groups') }}"
                            data-allow-clear="true"
                            multiple="multiple">
                        @foreach(($attributeGroupOptions ?? []) as $groupOption)
                            <option value="{{ $groupOption['id'] }}"
                                @selected(in_array((int) $groupOption['id'], $selectedAttributeGroupIds, true))>
                                {{ $groupOption['name'] }}
                            </option>
                        @endforeach
                    </select>
                </x-admin.form-group>

                <div id="property-attributes-container">
                    @include('property::admin.property.partials._attributes')
                </div>
            </div>
        </div>

        {{-- Slide categories --}}
        <div class="card card-flush mb-7">
            <div class="card-header">
                <div class="card-title">
                    <h2 class="d-flex align-items-center">
                        <i class="bi bi-images text-primary fs-3 me-2"></i>
                        {{ __('Slide categories') }}
                    </h2>
                </div>
            </div>
            <div class="card-body pt-3">
                <x-admin.form-group label="Slide categories" name="slide_category_ids"
                                    helper="{{ __('Choose categories, then upload property-specific media inside each category. Removing a category from this property also removes its uploaded media.') }}">
                    <input type="hidden" name="slide_category_ids_present" value="1"/>
                    <select name="slide_category_ids[]"
                            id="slide_category_ids"
                            class="form-select form-select-solid"
                            data-control="select2"
                            data-placeholder="{{ __('Select slide categories') }}"
                            data-allow-clear="true"
                            multiple="multiple">
                        @foreach(($slideCategories ?? []) as $slideCategory)
                            <option value="{{ $slideCategory['id'] }}"
                                @selected(in_array((int) $slideCategory['id'], $selectedSlideCategoryIds, true))>
                                {{ $slideCategory['name'] }}
                                @if($slideCategory['status'] !== \Modules\User\Enums\CmsStatus::PUBLISHED->value)
                                    — {{ __($slideCategory['status']) }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                </x-admin.form-group>

                <div id="property-slide-category-media" class="d-flex flex-column gap-5">
                    @foreach(($slideCategories ?? []) as $slideCategory)
                        @php
                            $categoryId = (int) $slideCategory['id'];
                            $categoryMedia = $existingSlideMediaByCategory->get($categoryId, collect());
                        @endphp
                        <div class="border rounded p-5 js-slide-category-media @if(! in_array($categoryId, $selectedSlideCategoryIds, true)) d-none @endif"
                             data-slide-category-id="{{ $categoryId }}">
                            <h4 class="fw-semibold mb-4">{{ $slideCategory['name'] }}</h4>

                            <div class="row g-5">
                                <div class="col-md-6">
                                    <x-admin.form-group label="Images" :name="'slide_media.'.$categoryId.'.images_media_paths'"
                                                        helper="{{ __('Choose up to 20 images from the Media Library for this property and category.') }}">
                                        <div class="js-slide-media-library"
                                             data-slide-category-id="{{ $categoryId }}"
                                             data-max="20">
                                            <div class="d-flex flex-wrap gap-2 mb-3 js-slide-media-selected"></div>
                                            <button type="button"
                                                    class="btn btn-light-primary btn-sm js-slide-media-pick">
                                                <i class="bi bi-images me-1"></i>{{ __('Choose from library') }}
                                            </button>
                                        </div>
                                    </x-admin.form-group>
                                </div>
                                <div class="col-md-6">
                                    <x-admin.form-group label="Videos" :name="'slide_media.'.$categoryId.'.videos'"
                                                        helper="{{ __('Upload up to 10 MP4, WebM, or MOV videos for this property and category.') }}">
                                        <input type="file"
                                               name="slide_media[{{ $categoryId }}][videos][]"
                                               class="form-control form-control-solid"
                                               accept="video/mp4,video/webm,video/quicktime"
                                               multiple/>
                                    </x-admin.form-group>
                                </div>
                            </div>

                            @if($categoryMedia->isNotEmpty())
                                <div class="row g-4 mt-1">
                                    @foreach($categoryMedia as $media)
                                        <div class="col-6 col-md-3 js-existing-slide-media"
                                             data-slide-media-id="{{ $media->id }}">
                                            <div class="border rounded p-2 h-100 position-relative">
                                                <button type="button"
                                                        class="btn btn-icon btn-sm btn-light-danger position-absolute top-0 end-0 m-1 js-existing-slide-media-remove"
                                                        title="{{ __('Remove') }}">
                                                    <i class="bi bi-x"></i>
                                                </button>
                                                @if($media->type === \Modules\Property\Models\PropertySlideMedia::TYPE_IMAGE)
                                                    <img src="{{ $media->url }}"
                                                         alt="{{ $slideCategory['name'] }}"
                                                         class="rounded object-fit-cover w-100"
                                                         style="height: 110px"/>
                                                @else
                                                    <a href="{{ $media->url }}"
                                                       target="_blank"
                                                       rel="noopener"
                                                       class="btn btn-light-primary btn-sm w-100">
                                                        <i class="bi bi-play-circle me-1"></i>{{ __('View video') }}
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Similar + YouTube --}}
        <div class="card card-flush mb-7">
            <div class="card-header">
                <div class="card-title">
                    <h2 class="d-flex align-items-center">
                        <i class="bi bi-link-45deg text-primary fs-3 me-2"></i>
                        {{ __('Related') }}
                    </h2>
                </div>
            </div>
            <div class="card-body pt-3">
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
            </div>
        </div>

        {{-- SEO --}}
        <div class="card card-flush mb-7">
            <div class="card-header">
                <div class="card-title">
                    <h2 class="d-flex align-items-center">
                        <i class="bi bi-search text-primary fs-3 me-2"></i>
                        {{ __('SEO settings') }}
                    </h2>
                </div>
            </div>
            <div class="card-body pt-3">
                @include('cms::admin.partials._seo_section', [
                    'metaTitle' => old('meta_title', $seoMeta['meta_title'] ?? ''),
                    'metaDescription' => old('meta_description', $seoMeta['meta_description'] ?? ''),
                    'metaKeywords' => old('meta_keywords', implode(', ', $seoMeta['meta_keywords'] ?? [])),
                    'metaImagePreview' => ! empty($seoMeta['meta_img']) ? asset('storage/' . $seoMeta['meta_img']) : null,
                    'metaImagePath' => $seoMeta['meta_img'] ?? null,
                    'titleSource' => '#title',
                    'descSource' => '#meta_description',
                    'slugSource' => '#url_key',
                    'baseUrl' => url('/').'/property/',
                ])

                @include('cms::admin.partials._seo_aside', [
                    'hasFeaturedImage' => $isEdit && ! empty($property->thumbnail),
                    'hasMetaImage' => ! empty($seoMeta['meta_img']),
                    'includeShortDescription' => false,
                    'slugTarget' => '#url_key',
                    'bodyTarget' => '#tinymce-overview, #tinymce-why-to-buy, #tinymce-content',
                    'featuredImageTarget' => "input[name='thumbnail'], input[name='thumbnail_media_path']",
                    'bodyLabel' => 'Body content',
                ])
            </div>
        </div>
    </div>

    <div class="col-xxl-4 col-xl-4">
        <div class="card card-flush mb-7">
            <div class="card-header">
                <div class="card-title">
                    <h2 class="d-flex align-items-center">
                        <i class="bi bi-toggles text-primary fs-3 me-2"></i>
                        {{ __('Flags') }}
                    </h2>
                </div>
            </div>
            <div class="card-body pt-3">
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

        <div class="d-flex justify-content-end gap-3">
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
            const ATTRIBUTE_GROUP_SCHEMA_URL = @json(route('admin.properties.attribute_group_schema'));
            const TYPE_DISTRICT = 'district';
            const TYPE_AREA = 'area';
            const IS_EDIT = @json($isEdit);
            const PROPERTY_ID = @json($currentPropertyId);
            const PREFILL_CITY_ID = @json($prefillCityId);
            const PREFILL_DISTRICT_ID = @json($prefillDistrictId);
            const PREFILL_AREA_ID = @json($selectedAreaIdValue);
            const CATALOG_ITEMS = @json($catalogItems);
            const OTHER_VALUE = '__other__';

            const $city = window.jQuery ? window.jQuery('#city_id') : null;
            const $district = window.jQuery ? window.jQuery('#district_id') : null;
            const $area = window.jQuery ? window.jQuery('#area_id') : null;

            async function fetchLocations(parentId, type, options = {}) {
                const params = new URLSearchParams();
                if (parentId !== null && parentId !== undefined && parentId !== '') {
                    params.set('parent_id', String(parentId));
                }
                params.set('type', type);
                if (options.forCity) {
                    params.set('for_city', '1');
                }
                const res = await fetch(`${LOCATION_CHILDREN_URL}?${params.toString()}`, {
                    headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
                });
                if (!res.ok) {
                    return [];
                }
                const data = await res.json();

                return Array.isArray(data.items) ? data.items : [];
            }

            function fillSelect($select, items, selectedId, placeholder, options = {}) {
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
                    if (item.parent_id !== null && item.parent_id !== undefined) {
                        o.dataset.parentId = String(item.parent_id);
                    }
                    if (current !== '' && String(item.id) === current) {
                        o.selected = true;
                    }
                    sel.appendChild(o);
                });
                $select.prop('disabled', items.length === 0);
                if (current) {
                    $select.val(current);
                }
                if (!options.silent) {
                    $select.trigger('change');
                }
            }

            async function loadAreasForSelection(selectedAreaId = '') {
                const cityId = $city?.val();
                const districtId = $district?.val();
                if (districtId) {
                    const areas = await fetchLocations(districtId, TYPE_AREA);
                    fillSelect($area, areas, selectedAreaId, @json(__('Select area')), {silent: true});
                    $area?.prop('disabled', areas.length === 0);

                    return;
                }
                if (cityId) {
                    const areas = await fetchLocations(cityId, TYPE_AREA, {forCity: true});
                    fillSelect($area, areas, selectedAreaId, @json(__('Select area')), {silent: true});
                    $area?.prop('disabled', areas.length === 0);

                    return;
                }
                fillSelect($area, [], '', @json(__('Select area')), {silent: true});
                $area?.prop('disabled', true);
            }

            async function onCityChange() {
                const cityId = $city?.val();
                fillSelect($district, [], '', @json(__('Select municipality')), {silent: true});
                fillSelect($area, [], '', @json(__('Select area')), {silent: true});
                if (!cityId) {
                    $district?.prop('disabled', true);
                    $area?.prop('disabled', true);

                    return;
                }
                const [districts, areas] = await Promise.all([
                    fetchLocations(cityId, TYPE_DISTRICT),
                    fetchLocations(cityId, TYPE_AREA, {forCity: true}),
                ]);
                fillSelect($district, districts, '', @json(__('Select municipality')), {silent: true});
                $district?.prop('disabled', districts.length === 0);
                fillSelect($area, areas, '', @json(__('Select area')), {silent: true});
                $area?.prop('disabled', areas.length === 0);
            }

            async function onDistrictChange() {
                await loadAreasForSelection('');
            }

            function onAreaChange() {
                const cityId = $city?.val();
                if (!$area?.length || !cityId || !$district?.length) {
                    return;
                }
                const selected = $area.find('option:selected');
                const parentId = selected?.attr('data-parent-id');
                if (!parentId) {
                    return;
                }
                // City-direct area: municipality is not required.
                if (String(parentId) === String(cityId)) {
                    if ($district.val()) {
                        $district.val('');
                    }

                    return;
                }
                // Area under a municipality: keep municipality in sync (no change event).
                if ($district.find(`option[value="${parentId}"]`).length
                    && String($district.val()) !== String(parentId)
                ) {
                    $district.val(String(parentId));
                }
            }

            function initLocationCascade() {
                if (!$city?.length) {
                    return;
                }
                $city.off('change.propertyLoc');
                $district.off('change.propertyLoc');
                $area?.off('change.propertyLoc');
                (async () => {
                    if (!PREFILL_CITY_ID) {
                        return;
                    }
                    $city.val(String(PREFILL_CITY_ID));
                    const districts = await fetchLocations(PREFILL_CITY_ID, TYPE_DISTRICT);
                    fillSelect($district, districts, PREFILL_DISTRICT_ID, @json(__('Select municipality')), {silent: true});
                    $district?.prop('disabled', districts.length === 0);
                    await loadAreasForSelection(PREFILL_AREA_ID);
                })().finally(() => {
                    $city.on('change.propertyLoc', onCityChange);
                    $district.on('change.propertyLoc', onDistrictChange);
                    $area?.on('change.propertyLoc', onAreaChange);
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

            async function loadAttributeGroups(groupIds) {
                const container = document.getElementById('property-attributes-container');
                if (!container) {
                    return;
                }
                const params = new URLSearchParams();
                (groupIds || []).forEach((id) => {
                    if (id) {
                        params.append('group_ids[]', String(id));
                    }
                });
                if (PROPERTY_ID) {
                    params.set('property_id', String(PROPERTY_ID));
                }
                container.classList.add('opacity-50');
                try {
                    const res = await fetch(`${ATTRIBUTE_GROUP_SCHEMA_URL}?${params.toString()}`, {
                        headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
                    });
                    if (!res.ok) {
                        return;
                    }
                    const data = await res.json();
                    container.innerHTML = data.html || '';
                    if (window.jQuery && window.jQuery.fn.select2) {
                        window.jQuery(container).find('[data-control="select2"]').select2();
                    }
                } finally {
                    container.classList.remove('opacity-50');
                }
            }

            function toggleSlideCategoryMedia(selectedIds) {
                const selected = new Set((selectedIds || []).map((id) => String(id)));
                document.querySelectorAll('.js-slide-category-media').forEach((panel) => {
                    const visible = selected.has(String(panel.dataset.slideCategoryId || ''));
                    panel.classList.toggle('d-none', !visible);
                    if (!visible) {
                        panel.querySelectorAll('input[type="file"]').forEach((input) => {
                            input.value = '';
                        });
                        const libraryRoot = panel.querySelector('.js-slide-media-library');
                        if (libraryRoot) {
                            libraryRoot.querySelector('.js-slide-media-selected').innerHTML = '';
                        }
                    }
                });
            }

            const propertyForm = document.getElementById('property-form');

            const slideMediaRemoveBucket = document.createElement('div');
            slideMediaRemoveBucket.id = 'js-slide-media-remove-bucket';
            slideMediaRemoveBucket.className = 'd-none';
            propertyForm?.appendChild(slideMediaRemoveBucket);

            @foreach($removedSlideMediaIds as $removedSlideMediaId)
                (() => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'remove_slide_media_ids[]';
                    input.value = @json((string) $removedSlideMediaId);
                    slideMediaRemoveBucket.appendChild(input);
                })();
            @endforeach

            function queueExistingSlideMediaRemoval(mediaId) {
                if (!mediaId || !slideMediaRemoveBucket) {
                    return;
                }
                const exists = [...slideMediaRemoveBucket.querySelectorAll('input')].some(
                    (input) => input.value === String(mediaId)
                );
                if (exists) {
                    return;
                }
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'remove_slide_media_ids[]';
                input.value = String(mediaId);
                slideMediaRemoveBucket.appendChild(input);
            }

            function bindExistingSlideMediaRemovers() {
                document.querySelectorAll('.js-existing-slide-media-remove').forEach((btn) => {
                    if (btn.dataset.bound === '1') {
                        return;
                    }
                    btn.dataset.bound = '1';
                    btn.addEventListener('click', () => {
                        const card = btn.closest('.js-existing-slide-media');
                        if (!card) {
                            return;
                        }
                        queueExistingSlideMediaRemoval(card.dataset.slideMediaId);
                        card.remove();
                    });
                });
            }

            function bindSlideMediaLibraryPickers() {
                document.querySelectorAll('.js-slide-media-library').forEach((root) => {
                    if (root.dataset.bound === '1') {
                        return;
                    }
                    root.dataset.bound = '1';
                    const selectedWrap = root.querySelector('.js-slide-media-selected');
                    const pickBtn = root.querySelector('.js-slide-media-pick');
                    const categoryId = root.dataset.slideCategoryId;
                    const max = parseInt(root.dataset.max || '20', 10);

                    function currentCount() {
                        return selectedWrap.querySelectorAll('input[name^="slide_media["]').length;
                    }

                    function addItem(item) {
                        if (!item || !item.path || currentCount() >= max) {
                            return;
                        }
                        const exists = [...selectedWrap.querySelectorAll('input')].some((input) => input.value === item.path);
                        if (exists) {
                            return;
                        }
                        const card = document.createElement('div');
                        card.className = 'border rounded p-2 position-relative js-slide-media-item';
                        card.style.width = '96px';
                        card.innerHTML =
                            '<img src="' + String(item.url || '').replace(/"/g, '&quot;') + '" alt="" class="rounded object-fit-cover w-100" style="height:72px">' +
                            '<input type="hidden" name="slide_media[' + categoryId + '][images_media_paths][]" value="' + String(item.path).replace(/"/g, '&quot;') + '">' +
                            '<button type="button" class="btn btn-icon btn-sm btn-light-danger position-absolute top-0 end-0 m-1 js-slide-media-remove" title="{{ __('Remove') }}"><i class="bi bi-x"></i></button>';
                        selectedWrap.appendChild(card);
                    }

                    pickBtn?.addEventListener('click', () => {
                        if (typeof window.openMediaLibraryPicker !== 'function') {
                            return;
                        }
                        window.openMediaLibraryPicker({
                            mode: 'multi',
                            max: Math.max(1, max - currentCount()),
                            onSelect: (items) => {
                                (items || []).forEach(addItem);
                            },
                        });
                    });

                    selectedWrap?.addEventListener('click', (event) => {
                        const removeBtn = event.target.closest('.js-slide-media-remove');
                        if (!removeBtn) {
                            return;
                        }
                        removeBtn.closest('.js-slide-media-item')?.remove();
                    });
                });
            }

            bindExistingSlideMediaRemovers();
            bindSlideMediaLibraryPickers();

            if (window.jQuery) {
                window.jQuery('#attribute_group_ids').on('change', function () {
                    loadAttributeGroups(window.jQuery(this).val() || []);
                });
                window.jQuery('#slide_category_ids').on('change', function () {
                    toggleSlideCategoryMedia(window.jQuery(this).val() || []);
                });
            }

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
                window.jQuery('#property_type_id, select[name="status"], #similar_property_ids, #slide_category_ids, #attribute_group_ids').select2();
            }
            initLocationCascade();

            document.addEventListener('click', function (event) {
                const removeMedia = event.target.closest('[data-attribute-media-remove]');
                if (removeMedia) {
                    const code = removeMedia.getAttribute('data-attribute-media-remove');
                    const removeInput = document.querySelector(`[name="attributes_remove[${code}]"]`);
                    const libraryInput = document.querySelector(`[name="attribute_media_path[${code}]"]`);
                    if (removeInput) removeInput.value = '1';
                    if (libraryInput) libraryInput.value = '';
                    const previewSelector = removeMedia.getAttribute('data-preview-target');
                    const preview = previewSelector ? document.querySelector(previewSelector) : null;
                    if (preview) preview.style.backgroundImage = 'url("' + @json(asset('images/default.jpg')) + '")';
                    removeMedia.closest('.d-flex')?.querySelector('a')?.classList.add('d-none');
                }

                const control = event.target.closest('.js-gallery-up, .js-gallery-down, .js-gallery-remove');
                if (!control) return;
                const item = control.closest('.js-gallery-item');
                if (!item) return;
                if (control.classList.contains('js-gallery-remove')) {
                    item.remove();
                } else if (control.classList.contains('js-gallery-up') && item.previousElementSibling) {
                    item.parentNode.insertBefore(item, item.previousElementSibling);
                } else if (control.classList.contains('js-gallery-down') && item.nextElementSibling) {
                    item.parentNode.insertBefore(item.nextElementSibling, item);
                }
            });
        })();
    </script>
@endpush
