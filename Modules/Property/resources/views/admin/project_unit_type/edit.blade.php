@section('title', __('Edit project unit type'))

@section('toolbar')
    @php
        $breadcrumbItems = [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard.index')],
            ['label' => __('Project unit types'), 'url' => route('admin.project_unit_types.index')],
            ['label' => __('Edit project unit type')],
        ];
    @endphp
    <x-admin.breadcrumb :pageTitle="__('Edit project unit type')" :breadcrumbItems="$breadcrumbItems"/>
@endsection

<x-admin-layout>
    <form method="POST" action="{{ route('admin.project_unit_types.update', $projectUnitType) }}">
        @csrf
        @method('PUT')

        <div class="card card-flush mb-7">
            <div class="card-header">
                <div class="card-title">
                    <h2 class="d-flex align-items-center">
                        <i class="bi bi-grid-3x2-gap text-primary fs-3 me-2"></i>
                        {{ __('General') }}
                    </h2>
                </div>
            </div>
            <div class="card-body pt-0">
                <x-admin.form-group label="Name" name="name" required translatable>
                    <input type="text" name="name" class="form-control form-control-solid"
                           value="{{ old('name', $projectUnitType->name) }}" placeholder="{{ __('e.g. 2+1') }}"/>
                </x-admin.form-group>

                <x-admin.form-group label="Sort order" name="sort_order"
                                    helper="{{ __('Lower numbers appear first in property forms.') }}">
                    <input type="number" name="sort_order" class="form-control form-control-solid" min="0" max="65535"
                           value="{{ old('sort_order', $projectUnitType->sort_order) }}"/>
                </x-admin.form-group>

                <x-admin.toggle-switch name="is_active" label="Active"
                                       :checked="(bool) old('is_active', $projectUnitType->is_active)" last/>
            </div>
        </div>

        <div class="d-flex justify-content-end py-6">
            <a href="{{ route('admin.project_unit_types.index') }}"
               class="btn btn-light btn-active-light-primary me-3">{{ __('Discard') }}</a>
            <button type="submit" class="btn btn-primary">
                <span class="indicator-label">{{ __('Save Changes') }}</span>
            </button>
        </div>
    </form>
</x-admin-layout>
