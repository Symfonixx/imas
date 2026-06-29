@section('title', __('Edit Location'))

@section('toolbar')
    @php
        $breadcrumbItems = [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard.index')],
            ['label' => __('Locations'), 'url' => route('admin.locations.index')],
            ['label' => __('Edit Location')],
        ];
    @endphp
    <x-admin.breadcrumb :pageTitle="__('Edit Location')" :breadcrumbItems="$breadcrumbItems"/>
@endsection

<x-admin-layout>
    <form method="POST" action="{{ route('admin.locations.update', $location) }}">
        @csrf
        @method('PUT')

        <div class="row gx-5 gx-xl-10">
            <div class="col-xxl-8 col-xl-8 mb-5 mb-xl-0">
                <div class="card card-flush mb-7">
                    <div class="card-header">
                        <div class="card-title">
                            <h2 class="d-flex align-items-center">
                                <i class="bi bi-geo-alt text-primary fs-3 me-2"></i>
                                {{ __('General') }}
                            </h2>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <x-admin.form-group label="Name" name="name" required translatable>
                            <input type="text"
                                   id="name"
                                   name="name"
                                   class="form-control form-control-solid"
                                   value="{{ old('name', $location->name) }}"
                                   placeholder="{{ __('Name') }}"/>
                        </x-admin.form-group>
                    </div>
                </div>
            </div>

            <div class="col-xxl-4 col-xl-4">
                <div class="card card-flush mb-7">
                    <div class="card-header">
                        <div class="card-title">
                            <h2 class="d-flex align-items-center">
                                <i class="bi bi-diagram-3 text-primary fs-3 me-2"></i>
                                {{ __('Hierarchy') }}
                            </h2>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <x-admin.form-group label="Parent" name="parent_id">
                            <select name="parent_id" class="form-select form-select-solid" data-control="select2"
                                    data-placeholder="{{ __('None (root)') }}">
                                <option value="">{{ __('None (root)') }}</option>
                                @foreach($parentOptions as $opt)
                                    <option value="{{ $opt->id }}" @selected((string) old('parent_id', $location->parent_id) === (string) $opt->id)>
                                        @if($opt->parent)
                                            {{ $opt->parent->name }} ›
                                        @endif
                                        {{ $opt->name }}
                                    </option>
                                @endforeach
                            </select>
                        </x-admin.form-group>

                        <x-admin.form-group label="Type" name="type" required>
                            <select name="type" class="form-select form-select-solid" required>
                                <option value="city" @selected(old('type', $location->type->value) === 'city')>{{ __('City') }}</option>
                                <option value="district" @selected(old('type', $location->type->value) === 'district')>{{ __('Municipality') }}</option>
                                <option value="area" @selected(old('type', $location->type->value) === 'area')>{{ __('Area') }}</option>
                            </select>
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
                    <div class="card-body pt-0">
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
            <a href="{{ route('admin.locations.index') }}"
               class="btn btn-light btn-active-light-primary me-3">{{ __('Discard') }}</a>
            <button type="submit" class="btn btn-primary" id="submit">
                <span class="indicator-label">{{ __('Save Changes') }}</span>
            </button>
        </div>
    </form>
</x-admin-layout>
