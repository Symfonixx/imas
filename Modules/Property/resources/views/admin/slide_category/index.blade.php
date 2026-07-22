@section('title', __('Slide categories'))

@section('toolbar')
    @php
        $breadcrumbItems = [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard.index')],
            ['label' => __('Properties'), 'url' => route('admin.properties.index')],
            ['label' => __('Slide categories')],
        ];
    @endphp
    <x-admin.breadcrumb :pageTitle="__('Slide categories')" :breadcrumbItems="$breadcrumbItems"/>
    <div class="d-flex align-items-center gap-2 gap-lg-3">
        <a class="btn btn-sm fw-bold btn-primary" href="{{ route('admin.slide_categories.create') }}">
            {{ __('Add slide category') }}
        </a>
    </div>
@endsection

<x-admin-layout>
    <x-admin.table :model="$model"
                   :search="__('Search in slide categories')"
                   :form-url="route('admin.slide_categories.deleteMulti')">
        <thead>
        <tr class="text-start text-muted fw-bold fs-7 gs-0">
            <th class="w-10px pe-2" data-orderable="false">
                <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                    <input class="form-check-input" type="checkbox" data-kt-check="true"
                           data-kt-check-target="#dataTable .form-check-input" value="1"/>
                </div>
            </th>
            <th class="min-w-220px">{{ __('Name') }}</th>
            <th class="min-w-160px">{{ __('Slug') }}</th>
            <th class="min-w-90px">{{ __('Position') }}</th>
            <th class="min-w-120px">{{ __('Status') }}</th>
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
                <td>
                    <div class="fw-bold">{{ $row->name }}</div>
                    @if($row->description)
                        <div class="text-muted fs-7">
                            {{ \Illuminate\Support\Str::limit(strip_tags($row->description), 80) }}
                        </div>
                    @endif
                </td>
                <td><code>{{ $row->slug }}</code></td>
                <td>{{ $row->position }}</td>
                <td>
                    <span class="badge badge-light-{{ $row->status === \Modules\User\Enums\CmsStatus::PUBLISHED ? 'success' : 'warning' }} fs-7 fw-bold">
                        {{ __($row->status->value) }}
                    </span>
                </td>
                <td class="text-end">
                    <a href="{{ route('admin.slide_categories.edit', $row) }}"
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
