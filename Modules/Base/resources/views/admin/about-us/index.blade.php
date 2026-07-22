@section('title', __('About Us Settings'))

@section('toolbar')
    @php
        $breadcrumbItems = [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard.index')],
            ['label' => 'Settings'],
            ['label' => 'About Us'],
        ];
    @endphp
    <x-admin.breadcrumb :pageTitle="__('About Us Settings')" :breadcrumbItems="$breadcrumbItems"/>
@endsection

@section('js')
    @include('base::shared._tinymce')
@endsection

@php
    $values = [
        'about_us_content' => (string) ($seo->get('about_us_content') ?: ''),
        'about_us_youtube_embed' => (string) ($seo->get('about_us_youtube_embed') ?: ''),
        'about_us_meta_title' => (string) ($seo->get('about_us_meta_title') ?: ''),
        'about_us_meta_description' => (string) ($seo->get('about_us_meta_description') ?: ''),
        'about_us_meta_keywords' => (string) ($seo->get('about_us_meta_keywords') ?: ''),
    ];

    $healthChecks = [
        [
            'target' => '#about_us_meta_title',
            'rule' => 'length',
            'min' => 30,
            'max' => 70,
            'hardMax' => 90,
            'label' => 'Meta title',
            'hint' => 'About page title shown in search results.',
        ],
        [
            'target' => '#about_us_meta_description',
            'rule' => 'length',
            'min' => 120,
            'max' => 160,
            'hardMax' => 255,
            'label' => 'Meta description',
            'hint' => 'Short page summary for search snippets.',
        ],
        [
            'target' => '#about_us_meta_keywords',
            'rule' => 'count',
            'min' => 3,
            'max' => 8,
            'label' => 'Meta keywords',
            'hint' => 'Use 3-8 focused keywords separated by commas.',
        ],
        [
            'target' => '#tinymce',
            'rule' => 'presence',
            'label' => 'About content',
            'hint' => 'Main About Us content should not be empty.',
        ],
    ];
@endphp

<x-admin-layout>
    <x-admin.seo-assets/>

    <form method="POST" action="{{ route('admin.about_us.store') }}" enctype="multipart/form-data" id="about-us-form">
        @csrf

        <div class="row gx-5 gx-xl-10">
            <div class="col-xxl-8 col-xl-8 mb-5 mb-xl-0">
                <div class="card card-flush mb-7">
                    <div class="card-header">
                        <div class="card-title">
                            <h2 class="d-flex align-items-center">
                                <i class="bi bi-image text-primary fs-3 me-2"></i>
                                {{ __('About Banner') }}
                            </h2>
                        </div>
                    </div>
                    <div class="card-body pt-3">
                        <x-admin.form-group label="About Us Banner" helper="Recommended dimensions: 1920px × 600px.">
                            <x-admin.image-input
                                name="imgs[about_us_banner]"
                                :preview="asset('storage/' . $settings->get('about_us_banner', 'default.jpg'))"
                                :mediaPath="$settings->get('about_us_banner')"
                                mediaInputName="imgs_media[about_us_banner]"/>
                        </x-admin.form-group>
                    </div>
                </div>

                <div class="card card-flush mb-7">
                    <div class="card-header">
                        <div class="card-title">
                            <h2 class="d-flex align-items-center">
                                <i class="bi bi-file-earmark-text text-primary fs-3 me-2"></i>
                                {{ __('About Content') }}
                            </h2>
                        </div>
                    </div>
                    <div class="card-body pt-3">
                        <x-admin.form-group label="Content" name="data.about_us_content" required translatable>
                            <textarea name="data[about_us_content]"
                                      id="tinymce"
                                      class="form-control form-control-solid">{!! old('data.about_us_content', $values['about_us_content']) !!}</textarea>
                        </x-admin.form-group>

                        <x-admin.form-group
                            label="YouTube Embed"
                            name="data.about_us_youtube_embed"
                            helper="Paste iframe embed code or a YouTube URL."
                            translatable>
                            <textarea name="data[about_us_youtube_embed]"
                                      rows="4"
                                      class="form-control form-control-solid">{{ old('data.about_us_youtube_embed', $values['about_us_youtube_embed']) }}</textarea>
                        </x-admin.form-group>
                    </div>
                </div>

                <div class="card card-flush mb-7">
                    <div class="card-header">
                        <div class="card-title">
                            <h2 class="d-flex align-items-center">
                                <i class="bi bi-search text-primary fs-3 me-2"></i>
                                {{ __('About SEO Metadata') }}
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
                                 data-title-source="#about_us_meta_title"
                                 data-desc-source="#about_us_meta_description"
                                 data-base-url="{{ url('/about-us') }}"
                                 data-default-title="{{ $values['about_us_meta_title'] }}"
                                 data-default-desc="{{ $values['about_us_meta_description'] }}">
                                <div class="preview-title">{{ $values['about_us_meta_title'] ?: __('Your title here') }}</div>
                                <div class="preview-url">{{ url('/about-us') }}</div>
                                <div class="preview-description">
                                    {{ $values['about_us_meta_description'] ?: __('Your meta description preview will appear here.') }}
                                </div>
                            </div>
                        </div>

                        <x-admin.seo-field
                            name="data[about_us_meta_title]"
                            id="about_us_meta_title"
                            error-key="data.about_us_meta_title"
                            label="Meta Title"
                            tip="About page title shown in search results."
                            optimal-label="Optimal: 30–70 characters"
                            :value="$values['about_us_meta_title']"
                            placeholder="About us - Brand Name"
                            translatable
                            :optimal-min="30"
                            :optimal-max="70"
                            :hard-max="90"
                            unit="characters"
                        />

                        <x-admin.seo-field
                            name="data[about_us_meta_description]"
                            id="about_us_meta_description"
                            error-key="data.about_us_meta_description"
                            label="Meta Description"
                            tip="Short page summary for search snippets."
                            optimal-label="Optimal: 120–160 characters"
                            :value="$values['about_us_meta_description']"
                            type="textarea"
                            rows="4"
                            placeholder="Learn more about our mission, values, and team..."
                            translatable
                            :optimal-min="120"
                            :optimal-max="160"
                            :hard-max="255"
                            unit="characters"
                        />

                        <x-admin.seo-field
                            name="data[about_us_meta_keywords]"
                            id="about_us_meta_keywords"
                            error-key="data.about_us_meta_keywords"
                            label="Meta Keywords"
                            tip="Use 3-8 focused keywords separated by commas."
                            optimal-label="Optimal: 3–8 keywords"
                            :value="$values['about_us_meta_keywords']"
                            placeholder="about us, company story, mission"
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
