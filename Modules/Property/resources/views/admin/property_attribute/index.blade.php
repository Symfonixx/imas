@section('title', __('Property attributes'))

@section('toolbar')
    @php
        $breadcrumbItems = [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard.index')],
            ['label' => __('Properties'), 'url' => '#'],
            ['label' => __('Property attributes')],
        ];
    @endphp
    <x-admin.breadcrumb :pageTitle="__('Property attributes')" :breadcrumbItems="$breadcrumbItems"/>
    <div class="d-flex align-items-center gap-2 gap-lg-3">
        <a class="btn btn-sm fw-bold btn-primary" href="{{ route('admin.property_attributes.create') }}">
            {{ __('Add attribute') }}
        </a>
    </div>
@endsection

<x-admin-layout>
    <x-admin.table :model="$model" :search="__('Search in property attributes')" :form-url="route('admin.property_attributes.deleteMulti')">
        <thead>
        <tr class="text-start text-muted fw-bold fs-7 gs-0">
            <th class="w-10px pe-2" data-orderable="false">
                <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                    <input class="form-check-input" type="checkbox" data-kt-check="true"
                           data-kt-check-target="#dataTable .form-check-input" value="1"/>
                </div>
            </th>
            <th>{{ __('Name') }}</th>
            <th>{{ __('Image') }}</th>
            <th>{{ __('Code') }}</th>
            <th>{{ __('Type') }}</th>
            <th>{{ __('Options') }}</th>
            <th>{{ __('Status') }}</th>
            <th class="text-end"></th>
        </tr>
        </thead>
        <tbody class="text-gray-600 fw-semibold">
        @foreach($model as $row)
            <tr>
                <td>
                    <div class="form-check form-check-sm form-check-custom form-check-solid">
                        <input class="form-check-input" type="checkbox" name="ids[]" value="{{ $row->id }}"/>
                    </div>
                </td>
                <td class="fw-bold">{{ $row->name }}</td>
                <td>
                    @if($row->image_link)
                        <img src="{{ $row->image_link }}"
                             alt="{{ $row->name }}"
                             width="36"
                             height="36"
                             class="rounded object-fit-cover"
                             style="width: 36px; height: 36px; object-fit: cover;"/>
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>
                <td><code>{{ $row->code }}</code></td>
                <td>{{ __(ucfirst($row->type->value)) }}</td>
                <td>{{ $row->options_count }}</td>
                <td>
                    <span class="badge badge-light-{{ $row->is_active ? 'success' : 'secondary' }}">
                        {{ $row->is_active ? __('Active') : __('Inactive') }}
                    </span>
                </td>
                <td class="text-end">
                    <a href="{{ route('admin.property_attributes.edit', $row) }}"
                       class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm">
                        <i class="ki-duotone ki-message-edit fs-1">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                    </a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </x-admin.table>
</x-admin-layout>
