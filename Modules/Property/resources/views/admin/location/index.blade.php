@section('title', __('Locations'))

@section('toolbar')
    @php
        $breadcrumbItems = [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard.index')],
            ['label' => __('Properties'), 'url' => '#'],
            ['label' => __('Locations')],
        ];
    @endphp
    <x-admin.breadcrumb :pageTitle="__('Locations')" :breadcrumbItems="$breadcrumbItems"/>
    <div class="d-flex align-items-center gap-2 gap-lg-3 flex-wrap">
        <a class="btn btn-sm fw-bold btn-primary" href="{{ route('admin.locations.create') }}">
            {{ __('Add Location') }}
        </a>
        <form method="get" action="{{ route('admin.locations.index') }}" class="d-flex align-items-center gap-2">
            <select name="type" class="form-select form-select-sm form-select-solid w-auto" onchange="this.form.submit()">
                <option value="">{{ __('All types') }}</option>
                <option value="city" @selected(request('type') === 'city')>{{ __('City') }}</option>
                <option value="district" @selected(request('type') === 'district')>{{ __('Municipality') }}</option>
                <option value="area" @selected(request('type') === 'area')>{{ __('Area') }}</option>
            </select>
        </form>
    </div>
@endsection

@push('styles')
    <style>
        #dataTable tbody tr.location-tree-row:hover {
            background-color: var(--bs-gray-100);
        }
        .location-tree-name-cell {
            max-width: 28rem;
        }
    </style>
@endpush

<x-admin-layout>
    <x-admin.table :search="__('Search In Locations')" :form-url="route('admin.locations.deleteMulti')">
        <thead>
        <tr class="text-start text-muted fw-bold fs-7 gs-0">
            <th class="w-10px pe-2" data-orderable="false">
                <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                    <input class="form-check-input" type="checkbox" data-kt-check="true"
                           data-kt-check-target="#dataTable .form-check-input" value="1"/>
                </div>
            </th>
            <th class="min-w-60px">{{ __('ID') }}</th>
            <th class="min-w-300px">{{ __('Name') }}</th>
            <th class="min-w-120px">{{ __('Type') }}</th>
            <th class="min-w-150px">{{ __('Created At') }}</th>
            <th class="min-w-100px text-end rounded-end"></th>
        </tr>
        </thead>
        <tbody class="text-gray-600 fw-semibold">
        @forelse($tree as $root)
            @include('property::admin.location._tree_rows', ['nodes' => collect([$root]), 'depth' => 0])
        @empty
            <tr>
                <td colspan="6" class="text-center text-muted py-10">{{ __('No locations found.') }}</td>
            </tr>
        @endforelse
        </tbody>
    </x-admin.table>

    @if(request()->filled('type'))
        <div class="text-muted fs-7 mt-3 px-2">
            {{ __('Filtered tree: only branches that include the selected type are shown.') }}
        </div>
    @endif
</x-admin-layout>
