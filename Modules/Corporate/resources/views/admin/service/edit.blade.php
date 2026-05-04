@section('title', __('Edit Service'))

@section('toolbar')
    @php
        $breadcrumbItems = [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard.index')],
            ['label' => __('Services'), 'url' => route('admin.corporate_services.index')],
            ['label' => __('Edit Service')],
        ];
    @endphp
    <x-admin.breadcrumb :pageTitle="__('Edit Service')" :breadcrumbItems="$breadcrumbItems"/>
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
    <form method="POST" action="{{ route('admin.corporate_services.update', $corporateService->id) }}"
          enctype="multipart/form-data"
          id="corporate-service-form">
        @csrf
        @method('PUT')

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
                    <div class="card-body pt-0">
                        <x-admin.form-group label="Title" name="title" required translatable>
                            <input type="text"
                                   id="title"
                                   name="title"
                                   class="form-control form-control-solid"
                                   value="{{ old('title', $corporateService->title) }}"
                                   placeholder="{{ __('Title') }}"/>
                        </x-admin.form-group>

                        <x-admin.form-group label="Url" name="slug">
                            <input type="text" id="slug" name="slug" value="{{ $corporateService->slug }}"
                                   class="form-control form-control-solid" readonly/>
                        </x-admin.form-group>

                        <x-admin.form-group label="Featured Image"
                                            helper="Recommended dimensions: 900px × 600px.">
                            <x-admin.image-input name="img" :preview="$corporateService->image_link"/>
                        </x-admin.form-group>

                        <x-admin.seo-field
                            name="description"
                            label="Short Description"
                            tip="Write a clear, concise summary (120–160 chars) — it may be shown in search results."
                            optimal-label="Optimal: 120–160 characters"
                            :value="old('description', $corporateService->description)"
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
                                      id="tinymce">{!! old('content', $corporateService->content) !!}</textarea>
                        </x-admin.form-group>
                    </div>
                </div>

                @include('cms::admin.partials._seo_section', [
                    'metaTitle' => old('meta_title', $corporateService->meta_title),
                    'metaDescription' => old('meta_description', $corporateService->meta_description),
                    'metaKeywords' => old('meta_keywords', $corporateService->meta_keywords),
                    'metaImagePreview' => $corporateService->meta_image_link,
                    'titleSource' => '#title',
                    'descSource' => '#meta_description',
                    'slugSource' => '#slug',
                    'baseUrl' => url('/') . '/services/',
                ])
            </div>

            <div class="col-xxl-4 col-xl-4">
                @include('cms::admin.partials._status_aside', [
                    'isActive' => old('publish', $corporateService->status) === 'Published',
                    'isFeatured' => (bool) old('featured', $corporateService->featured),
                    'showTranslations' => true,
                    'updateTranslations' => (bool) old('update_translations', false),
                ])

                @include('cms::admin.partials._seo_aside', [
                    'hasFeaturedImage' => (bool) $corporateService->image,
                    'hasMetaImage' => (bool) $corporateService->meta_image,
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
