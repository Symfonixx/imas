@php
    $defaultIcon = $iconChoices[0]['class'] ?? 'bi bi-boxes';
    $iconSelected = old('icon', $defaultIcon);
@endphp

@section('title', __('Add property type'))

@section('toolbar')
    @php
        $breadcrumbItems = [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard.index')],
            ['label' => __('Property types'), 'url' => route('admin.property_types.index')],
            ['label' => __('Add property type')],
        ];
    @endphp
    <x-admin.breadcrumb :pageTitle="__('Add property type')" :breadcrumbItems="$breadcrumbItems"/>
@endsection

<x-admin-layout>
    <form method="POST" action="{{ route('admin.property_types.store') }}">
        @csrf
        <div class="row gx-5 gx-xl-10">
            <div class="col-xxl-8 col-xl-8 mb-5 mb-xl-0">
                <div class="card card-flush mb-7">
                    <div class="card-header">
                        <div class="card-title">
                            <h2 class="d-flex align-items-center">
                                <i class="bi bi-tag text-primary fs-3 me-2"></i>
                                {{ __('General') }}
                            </h2>
                        </div>
                    </div>
                    <div class="card-body pt-3">
                        <x-admin.form-group label="Name" name="name" required translatable>
                            <input type="text"
                                   id="property_type_name"
                                   name="name"
                                   class="form-control form-control-solid"
                                   value="{{ old('name') }}"
                                   placeholder="{{ __('Name') }}"/>
                        </x-admin.form-group>

                        <x-admin.form-group label="URL slug" name="slug" required
                                            helper="{{ __('Lowercase, hyphens only. Used in URLs.') }}">
                            <input type="text"
                                   name="slug"
                                   class="form-control form-control-solid"
                                   value="{{ old('slug') }}"
                                   placeholder="residential-rent"
                                   pattern="[a-z0-9]+(-[a-z0-9]+)*"
                                   maxlength="191"/>
                        </x-admin.form-group>
                    </div>
                </div>
            </div>

            <div class="col-xxl-4 col-xl-4">
                <div class="card card-flush mb-7">
                    <div class="card-header">
                        <div class="card-title">
                            <h2 class="d-flex align-items-center">
                                <i class="bi bi-palette text-primary fs-3 me-2"></i>
                                {{ __('Appearance') }}
                            </h2>
                        </div>
                    </div>
                    <div class="card-body pt-3">
                        <x-admin.form-group label="Icon" name="icon" required
                                            helper="{{ __('Shown to users when choosing a property type.') }}">
                            @include('property::admin.partials.bootstrap_icon_picker', [
                                'name' => 'icon',
                                'iconChoices' => $iconChoices,
                                'selected' => $iconSelected,
                            ])
                        </x-admin.form-group>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end py-6">
            <a href="{{ route('admin.property_types.index') }}"
               class="btn btn-light btn-active-light-primary me-3">{{ __('Discard') }}</a>
            <button type="submit" class="btn btn-primary">
                <span class="indicator-label">{{ __('Save Changes') }}</span>
            </button>
        </div>
    </form>
</x-admin-layout>
