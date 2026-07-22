@section('title', __('Turkish Citizenship Settings'))

@section('toolbar')
    @php
        $breadcrumbItems = [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard.index')],
            ['label' => 'Properties'],
            ['label' => 'Turkish Citizenship'],
        ];
    @endphp
    <x-admin.breadcrumb :pageTitle="__('Turkish Citizenship Settings')" :breadcrumbItems="$breadcrumbItems"/>
@endsection

@section('js')
    @include('base::shared._tinymce')
@endsection

@php
    $values = [
        'turkish_citizenship_content' => (string) ($seo->get('turkish_citizenship_content') ?: ''),
        'turkish_citizenship_youtube_embed' => (string) ($seo->get('turkish_citizenship_youtube_embed') ?: ''),
        'turkish_citizenship_meta_title' => (string) ($seo->get('turkish_citizenship_meta_title') ?: ''),
        'turkish_citizenship_meta_description' => (string) ($seo->get('turkish_citizenship_meta_description') ?: ''),
        'turkish_citizenship_meta_keywords' => (string) ($seo->get('turkish_citizenship_meta_keywords') ?: ''),
    ];

    $healthChecks = [
        [
            'target' => '#turkish_citizenship_meta_title',
            'rule' => 'length',
            'min' => 30,
            'max' => 70,
            'hardMax' => 90,
            'label' => 'Meta title',
            'hint' => 'Page title shown in search results.',
        ],
        [
            'target' => '#turkish_citizenship_meta_description',
            'rule' => 'length',
            'min' => 120,
            'max' => 160,
            'hardMax' => 255,
            'label' => 'Meta description',
            'hint' => 'Short page summary for search snippets.',
        ],
        [
            'target' => '#turkish_citizenship_meta_keywords',
            'rule' => 'count',
            'min' => 3,
            'max' => 8,
            'label' => 'Meta keywords',
            'hint' => 'Use 3-8 focused keywords separated by commas.',
        ],
        [
            'target' => '#tinymce',
            'rule' => 'presence',
            'label' => 'Content',
            'hint' => 'Main page content should not be empty.',
        ],
    ];
@endphp

<x-admin-layout>
    <x-admin.seo-assets/>

    <form method="POST"
          action="{{ route('admin.turkish_citizenship.store') }}"
          enctype="multipart/form-data"
          id="turkish-citizenship-form">
        @csrf

        <div class="row gx-5 gx-xl-10">
            <div class="col-xxl-8 col-xl-8 mb-5 mb-xl-0">
                <div class="card card-flush mb-7">
                    <div class="card-header">
                        <div class="card-title">
                            <h2 class="d-flex align-items-center">
                                <i class="bi bi-image text-primary fs-3 me-2"></i>
                                {{ __('Banner') }}
                            </h2>
                        </div>
                    </div>
                    <div class="card-body pt-3">
                        <x-admin.form-group label="Turkish Citizenship Banner"
                                            helper="Recommended dimensions: 1920px × 600px.">
                            <x-admin.image-input
                                name="imgs[turkish_citizenship_banner]"
                                :preview="asset('storage/' . $settings->get('turkish_citizenship_banner', 'default.jpg'))"
                                :mediaPath="$settings->get('turkish_citizenship_banner')"
                                mediaInputName="imgs_media[turkish_citizenship_banner]"/>
                        </x-admin.form-group>
                    </div>
                </div>

                <div class="card card-flush mb-7">
                    <div class="card-header">
                        <div class="card-title">
                            <h2 class="d-flex align-items-center">
                                <i class="bi bi-file-earmark-text text-primary fs-3 me-2"></i>
                                {{ __('Content') }}
                            </h2>
                        </div>
                    </div>
                    <div class="card-body pt-3">
                        <x-admin.form-group label="Content" name="data.turkish_citizenship_content" required translatable>
                            <textarea name="data[turkish_citizenship_content]"
                                      id="tinymce"
                                      class="form-control form-control-solid">{!! old('data.turkish_citizenship_content', $values['turkish_citizenship_content']) !!}</textarea>
                        </x-admin.form-group>

                        <x-admin.form-group
                            label="YouTube Embed"
                            name="data.turkish_citizenship_youtube_embed"
                            helper="Paste iframe embed code or a YouTube URL."
                            translatable>
                            <textarea name="data[turkish_citizenship_youtube_embed]"
                                      rows="4"
                                      class="form-control form-control-solid">{{ old('data.turkish_citizenship_youtube_embed', $values['turkish_citizenship_youtube_embed']) }}</textarea>
                        </x-admin.form-group>
                    </div>
                </div>

                <div class="card card-flush mb-7">
                    <div class="card-header">
                        <div class="card-title">
                            <h2 class="d-flex align-items-center">
                                <i class="bi bi-search text-primary fs-3 me-2"></i>
                                {{ __('SEO Metadata') }}
                            </h2>
                        </div>
                    </div>
                    <div class="card-body pt-3">
                        <div class="mb-7">
                            <label class="form-label fw-semibold fs-6">
                                {{ __('Search engine listing preview') }}
                            </label>
                            <div class="cms-search-preview"
                                 data-seo-preview="true"
                                 data-title-source="#turkish_citizenship_meta_title"
                                 data-desc-source="#turkish_citizenship_meta_description"
                                 data-base-url="{{ url('/turkish-citizenship') }}"
                                 data-default-title="{{ $values['turkish_citizenship_meta_title'] }}"
                                 data-default-desc="{{ $values['turkish_citizenship_meta_description'] }}">
                                <div class="preview-title">{{ $values['turkish_citizenship_meta_title'] ?: __('Your title here') }}</div>
                                <div class="preview-url">{{ url('/turkish-citizenship') }}</div>
                                <div class="preview-description">
                                    {{ $values['turkish_citizenship_meta_description'] ?: __('Your meta description preview will appear here.') }}
                                </div>
                            </div>
                        </div>

                        <x-admin.seo-field
                            name="data[turkish_citizenship_meta_title]"
                            id="turkish_citizenship_meta_title"
                            error-key="data.turkish_citizenship_meta_title"
                            label="Meta Title"
                            tip="Page title shown in search results."
                            optimal-label="Optimal: 30-70 characters"
                            :value="$values['turkish_citizenship_meta_title']"
                            placeholder="Turkish citizenship by investment - Brand Name"
                            translatable
                            :optimal-min="30"
                            :optimal-max="70"
                            :hard-max="90"
                            unit="characters"
                        />

                        <x-admin.seo-field
                            name="data[turkish_citizenship_meta_description]"
                            id="turkish_citizenship_meta_description"
                            error-key="data.turkish_citizenship_meta_description"
                            label="Meta Description"
                            tip="Short page summary for search snippets."
                            optimal-label="Optimal: 120-160 characters"
                            :value="$values['turkish_citizenship_meta_description']"
                            type="textarea"
                            rows="4"
                            placeholder="Discover requirements, process, and benefits of Turkish citizenship by investment."
                            translatable
                            :optimal-min="120"
                            :optimal-max="160"
                            :hard-max="255"
                            unit="characters"
                        />

                        <x-admin.seo-field
                            name="data[turkish_citizenship_meta_keywords]"
                            id="turkish_citizenship_meta_keywords"
                            error-key="data.turkish_citizenship_meta_keywords"
                            label="Meta Keywords"
                            tip="Use 3-8 focused keywords separated by commas."
                            optimal-label="Optimal: 3-8 keywords"
                            :value="$values['turkish_citizenship_meta_keywords']"
                            placeholder="turkish citizenship, citizenship by investment, turkey passport"
                            translatable
                            :optimal-min="3"
                            :optimal-max="8"
                            unit="keywords"
                        />
                    </div>
                </div>
            </div>

            <div class="col-xxl-4 col-xl-4">
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
                        <div class="form-check form-check-custom form-check-solid">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="update_translations"
                                   id="update_translations"
                                   value="1"
                                   @checked(old('update_translations'))/>
                            <label class="form-check-label fs-7 ms-2" for="update_translations">
                                {{ __('Use Google Translate to update all other languages.') }}
                            </label>
                        </div>
                    </div>
                </div>

                <x-admin.seo-aside :checks="$healthChecks"/>
            </div>
        </div>

        <div class="d-flex justify-content-end py-6">
            <a href="{{ url()->previous() }}"
               class="btn btn-light btn-active-light-primary me-3">{{ __('Discard') }}</a>
            <button type="submit" class="btn btn-primary" id="submit">
                <span class="indicator-label">{{ __('Save Changes') }}</span>
            </button>
        </div>
    </form>
</x-admin-layout>
