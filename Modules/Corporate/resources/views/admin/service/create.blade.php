@section('title', __('Add New Service'))

@section('toolbar')
    @php
        $breadcrumbItems = [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard.index')],
            ['label' => __('Services'), 'url' => route('admin.corporate_services.index')],
            ['label' => __('Add New Service')],
        ];
    @endphp
    <x-admin.breadcrumb :pageTitle="__('Add New Service')" :breadcrumbItems="$breadcrumbItems"/>
@endsection

@section('js')
    @include('base::shared._tinymce')
    <script>
        $(document).ready(function () {
            var keywordsInput = document.querySelector('#meta_keywords');
            if (keywordsInput && typeof Tagify !== 'undefined') {
                new Tagify(keywordsInput);
            }
        });
    </script>
@endsection

@include('cms::admin.partials._seo_assets')

<x-admin-layout>
    <form method="POST" action="{{ route('admin.corporate_services.store') }}" enctype="multipart/form-data"
          id="corporate-service-form">
        @csrf
        <div class="row gx-5 gx-xl-10">
            <div class="col-xxl-8 col-xl-8 mb-5 mb-xl-0">
                <div class="card card-flush mb-7">
                    <div class="card-header">
                        <div class="card-title">
                            <h2 class="d-flex align-items-center">
                                <i class="bi bi-briefcase text-primary fs-3 me-2"></i>
                                {{ __('General') }}
                            </h2>
                        </div>
                    </div>
                    <div class="card-body pt-3">
                        <x-admin.form-group label="Title" name="title" required translatable>
                            <input type="text"
                                   id="title"
                                   name="title"
                                   class="form-control form-control-solid"
                                   value="{{ old('title') }}"
                                   placeholder="{{ __('Title') }}"/>
                        </x-admin.form-group>

                        <x-admin.form-group label="Url" name="slug" required
                                            helper="SEO Tip: Keep the URL short, lowercase, and use hyphens to separate words. Avoid stop words.">
                            <input type="text"
                                   id="gslug"
                                   name="gslug"
                                   class="form-control form-control-solid mb-3"
                                   value="{{ old('slug') }}"
                                   placeholder="our-consulting-service"/>
                            <input type="hidden" name="slug" value="{{ old('slug') }}" id="slug">
                            <div class="text-muted fs-7" id="link">{{ old('slug') }}</div>
                        </x-admin.form-group>

                        <x-admin.form-group label="Featured Image" required
                                            helper="Recommended dimensions: 900px × 600px.">
                            <x-admin.image-input name="img" required/>
                        </x-admin.form-group>

                        <x-admin.seo-field
                            name="description"
                            label="Short Description"
                            tip="Write a clear, concise summary (120–160 chars) — it may be shown in search results."
                            optimal-label="Optimal: 120–160 characters"
                            :value="old('description', '')"
                            type="textarea"
                            placeholder="{{ __('Short Description') }}..."
                            translatable
                            required
                            :optimal-min="120"
                            :optimal-max="160"
                            :hard-max="500"
                            unit="characters"
                        />

                        <x-admin.form-group label="Content" name="content" required translatable>
                            <textarea name="content"
                                      class="form-control form-control-solid"
                                      id="tinymce">{!! old('content') !!}</textarea>
                        </x-admin.form-group>
                    </div>
                </div>

                @include('cms::admin.partials._seo_section', [
                    'metaTitle' => old('meta_title', ''),
                    'metaDescription' => old('meta_description', ''),
                    'metaKeywords' => old('meta_keywords', ''),
                    'metaImagePreview' => null,
                    'titleSource' => '#title',
                    'descSource' => '#meta_description',
                    'slugSource' => '#slug',
                    'baseUrl' => url('/') . '/services/',
                ])
            </div>

            <div class="col-xxl-4 col-xl-4">
                @include('cms::admin.partials._status_aside', [
                    'isActive' => old('publish', 'Published') === 'Published',
                    'isFeatured' => (bool) old('featured', false),
                    'showTranslations' => false,
                ])

                @include('cms::admin.partials._seo_aside', [
                    'hasFeaturedImage' => false,
                    'hasMetaImage' => false,
                    'includeShortDescription' => true,
                ])
            </div>
        </div>

        <div class="d-flex justify-content-end py-6">
            <a href="{{ route('admin.corporate_services.index') }}"
               class="btn btn-light btn-active-light-primary me-3">{{ __('Discard') }}</a>
            <button type="submit" class="btn btn-primary" id="submit">
                <span class="indicator-label">{{ __('Save Changes') }}</span>
            </button>
        </div>
    </form>
</x-admin-layout>
