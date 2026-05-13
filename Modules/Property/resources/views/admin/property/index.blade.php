@section('title', __('Properties'))

@section('toolbar')
    @php
        $breadcrumbItems = [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard.index')],
            ['label' => __('Properties')],
        ];
    @endphp
    <x-admin.breadcrumb :pageTitle="__('Properties')" :breadcrumbItems="$breadcrumbItems"/>
    <div class="d-flex align-items-center gap-2 gap-lg-3">
        <a class="btn btn-sm fw-bold btn-primary" href="{{ route('admin.properties.create') }}">
            {{ __('Add property') }}
        </a>
    </div>
@endsection

<x-admin-layout>
    <x-admin.table :model="$model" :search="__('Search in properties')" :form-url="route('admin.properties.deleteMulti')">
        <thead>
        <tr class="text-start text-muted fw-bold fs-7 gs-0">
            <th class="w-10px pe-2" data-orderable="false">
                <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                    <input class="form-check-input" type="checkbox" data-kt-check="true"
                           data-kt-check-target="#dataTable .form-check-input" value="1"/>
                </div>
            </th>
            <th>{{ __('Thumbnail') }}</th>
            <th>{{ __('Project name') }}</th>
            <th>{{ __('Project code') }}</th>
            <th>{{ __('Property type') }}</th>
            <th>{{ __('Location') }}</th>
            <th>{{ __('Status') }}</th>
            <th class="text-end rounded-end"></th>
        </tr>
        </thead>
        <tbody class="text-gray-600 fw-semibold">
        @foreach($model as $row)
            @php
                $listName = $row->project_name ?: $row->title;
            @endphp
            <tr>
                <td>
                    <div class="form-check form-check-sm form-check-custom form-check-solid">
                        <input class="form-check-input" type="checkbox" name="ids[]" value="{{ $row->id }}"/>
                    </div>
                </td>
                <td>
                    <img src="{{ $row->thumbnail ? asset('storage/' . $row->thumbnail) : asset('images/blank.png') }}"
                         class="w-60px h-60px object-fit-cover rounded"
                         alt="{{ $listName }}"/>
                </td>
                <td class="fw-bold">{{ $listName }}</td>
                <td><code>{{ $row->project_code }}</code></td>
                <td>{{ $row->propertyType?->name ?? '—' }}</td>
                <td>{{ $row->location?->name ?? '—' }}</td>
                <td>
                    @if(($row->status?->value ?? $row->status) === 'Published')
                        <span class="badge badge-light-success">{{ __('Published') }}</span>
                    @else
                        <span class="badge badge-light-secondary">{{ __('Archived') }}</span>
                    @endif
                </td>
                <td class="text-end">
                    <a href="{{ route('admin.properties.edit', $row) }}"
                       class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1">
                        <i class="ki-duotone ki-message-edit fs-1">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </x-admin.table>
</x-admin-layout>
