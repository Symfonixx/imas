@section('title', __('Attributes'))

@section('toolbar')
    @php
        $breadcrumbItems = [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard.index')],
            ['label' => __('Properties'), 'url' => '#'],
            ['label' => __('Attributes')],
        ];
    @endphp
    <x-admin.breadcrumb :pageTitle="__('Attributes')" :breadcrumbItems="$breadcrumbItems"/>
    <div class="d-flex align-items-center gap-2 gap-lg-3 flex-wrap">
        <a class="btn btn-sm fw-bold btn-primary" href="{{ route('admin.attributes.create') }}">
            {{ __('Add attribute') }}
        </a>
        <form method="get" action="{{ route('admin.attributes.index') }}" class="d-flex align-items-center gap-2">
            <select name="type" class="form-select form-select-sm form-select-solid w-auto" onchange="this.form.submit()">
                <option value="">{{ __('All types') }}</option>
                @foreach(\Modules\Property\Enums\AttributeType::cases() as $case)
                    <option value="{{ $case->value }}" @selected(request('type') === $case->value)>
                        {{ __($case->value) }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>
@endsection

<x-admin-layout>
    <x-admin.table :model="$model" :search="__('Search in attributes')" :form-url="route('admin.attributes.deleteMulti')">
        <thead>
        <tr class="text-start text-muted fw-bold fs-7 gs-0">
            <th class="w-10px pe-2" data-orderable="false">
                <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                    <input class="form-check-input" type="checkbox" data-kt-check="true"
                           data-kt-check-target="#dataTable .form-check-input" value="1"/>
                </div>
            </th>
            <th class="min-w-200px">{{ __('Name') }}</th>
            <th class="min-w-120px">{{ __('Code') }}</th>
            <th class="min-w-100px">{{ __('Type') }}</th>
            <th class="min-w-100px">{{ __('Filterable') }}</th>
            <th class="min-w-100px">{{ __('Required') }}</th>
            <th class="min-w-100px">{{ __('Translatable') }}</th>
            <th class="min-w-150px">{{ __('Created At') }}</th>
            <th class="min-w-100px text-end rounded-end"></th>
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
                <td><code>{{ $row->code }}</code></td>
                <td><span class="badge badge-light-primary fs-7 fw-bold">{{ __($row->type->value) }}</span></td>
                <td>{{ $row->is_filterable ? __('Yes') : __('No') }}</td>
                <td>{{ $row->is_required ? __('Yes') : __('No') }}</td>
                <td>{{ $row->is_trans ? __('Yes') : __('No') }}</td>
                <td>{{ $row->created_at->diffForHumans() }}</td>
                <td class="text-end">
                    <a href="{{ route('admin.attributes.edit', $row) }}"
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
