@section('title', __('Edit Slide'))

@section('toolbar')
    @php
        $breadcrumbItems = [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard.index')],
            ['label' => 'Slides', 'url' => route('admin.slides.index')],
            ['label' => 'Edit Slide'],
        ];
    @endphp
    <x-admin.breadcrumb :pageTitle="__('Edit Slide')" :breadcrumbItems="$breadcrumbItems"/>
@endsection

<x-admin-layout>
    <form method="POST" action="{{ route('admin.slides.update', $slide->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row gx-5 gx-xl-10">
            <div class="col-xxl-8 col-xl-8 mb-5 mb-xl-0">
                <div class="card card-flush mb-7">
                    <div class="card-header">
                        <div class="card-title">
                            <h2 class="d-flex align-items-center">
                                <i class="bi bi-images text-primary fs-3 me-2"></i>
                                {{ __('General') }}
                            </h2>
                        </div>
                    </div>
                    <div class="card-body pt-3">
                        <x-admin.form-group label="Image" name="img"
                                            helper="Recommended wide banner ratio for hero slides.">
                            <x-admin.image-input name="img" :preview="$slide->image_link" :mediaPath="$slide->image"/>
                        </x-admin.form-group>

                        <x-admin.form-group label="Main Title" name="main_title" translatable>
                            <input type="text"
                                   id="main_title"
                                   name="main_title"
                                   class="form-control form-control-solid"
                                   value="{{ old('main_title', $slide->main_title) }}"
                                   placeholder="{{ __('Main Title') }}"/>
                        </x-admin.form-group>

                        <x-admin.form-group label="Subtitle" name="subtitle" translatable>
                            <textarea name="subtitle"
                                      id="subtitle"
                                      rows="3"
                                      class="form-control form-control-solid"
                                      placeholder="{{ __('Subtitle') }}">{{ old('subtitle', $slide->subtitle) }}</textarea>
                        </x-admin.form-group>

                        <x-admin.form-group label="Link" name="link"
                                            helper="Optional URL when the slide is clickable.">
                            <input type="url"
                                   name="link"
                                   class="form-control form-control-solid"
                                   value="{{ old('link', $slide->link) }}"
                                   placeholder="https://"/>
                        </x-admin.form-group>
                    </div>
                </div>
            </div>

            <div class="col-xxl-4 col-xl-4">
                <div class="card card-flush mb-7">
                    <div class="card-header">
                        <div class="card-title">
                            <h2 class="d-flex align-items-center">
                                <i class="bi bi-toggles text-primary fs-3 me-2"></i>
                                {{ __('Status') }}
                            </h2>
                        </div>
                    </div>
                    <div class="card-body pt-3">
                        <x-admin.toggle-switch
                            name="publish"
                            label="Active"
                            helper="When active, the content is visible to the public."
                            icon="bi bi-broadcast-pin"
                            tone="success"
                            :checked="old('publish', $slide->status) === 'Published'"
                            value="Published"
                            last
                        />
                    </div>
                </div>

                <div class="card card-flush mb-7">
                    <div class="card-header">
                        <div class="card-title">
                            <h2 class="d-flex align-items-center">
                                <i class="bi bi-sort-numeric-down text-primary fs-3 me-2"></i>
                                {{ __('Rank') }}
                            </h2>
                        </div>
                    </div>
                    <div class="card-body pt-3">
                        <x-admin.form-group label="Rank" name="rank" required>
                            <input type="number"
                                   min="0"
                                   name="rank"
                                   class="form-control form-control-solid"
                                   value="{{ old('rank', $slide->rank) }}"
                                   placeholder="0"/>
                        </x-admin.form-group>
                    </div>
                </div>

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
                                   @checked(old('update_translations', false))/>
                            <label class="form-check-label fs-7 ms-2" for="update_translations">
                                {{ __('Use Google Translate to update all other languages.') }}
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end py-6">
            <a href="{{ route('admin.slides.index') }}"
               class="btn btn-light btn-active-light-primary me-3">{{ __('Discard') }}</a>
            <button type="submit" class="btn btn-primary" id="submit">
                <span class="indicator-label">{{ __('Save Changes') }}</span>
            </button>
        </div>
    </form>
</x-admin-layout>
