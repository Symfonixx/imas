@section('title', __('Services'))

@section('toolbar')
    @php
        $breadcrumbItems = [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard.index')],
            ['label' => __('Corporate'), 'url' => '#'],
            ['label' => __('Services')],
        ];
    @endphp
    <x-admin.breadcrumb :pageTitle="__('Services')" :breadcrumbItems="$breadcrumbItems"/>
    <div class="d-flex align-items-center gap-2 gap-lg-3">
        <a class="btn btn-sm fw-bold btn-primary" href="{{ route('admin.corporate_services.create') }}">
            {{ __('Add New Service') }}
        </a>
    </div>
@endsection
@section('js')
@endsection
<x-admin-layout>
    <x-admin.table :model="$model" search="Search In Services" :form-url="route('admin.corporate_services.deleteMulti')">
        <thead>
        <tr class="text-start text-muted fw-bold fs-7 gs-0">
            <th class="w-10px pe-2" data-orderable="false">
                <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                    <input class="form-check-input" type="checkbox" data-kt-check="true"
                           data-kt-check-target="#dataTable .form-check-input" value="1"/>
                </div>
            </th>
            <th class="min-w-200px"></th>
            <th class="min-w-200px">{{ __('Title') }}</th>
            <th class="min-w-150px">{{ __('Featured') }}</th>
            <th class="min-w-150px">{{ __('Publish Status') }}</th>
            <th class="min-w-150px">{{ __('Created At') }}</th>
            <th class="min-w-150px"><i class="bi bi-eye text-primary fa-2x"></i></th>
            <th class="min-w-200px text-end rounded-end"></th>
        </thead>
        <tbody class="text-gray-600 fw-semibold">
        @foreach($model as $service)
            <tr>
                <td>
                    <div class="form-check form-check-sm form-check-custom form-check-solid">
                        <input class="form-check-input" type="checkbox" name="ids[]" value="{{ $service->id }}"/>
                    </div>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <a href="{{ $service->image_link }}" target="_blank" class="symbol me-5">
                            <img src="{{ $service->image_link }}" alt="{{ $service->title }}"/>
                        </a>
                    </div>
                </td>
                <td>
                    <h5>
                        <span class="fw-bolder text-hover-primary mb-1 fs-6">{{ $service->title }}</span>
                    </h5>
                </td>
                <td>
                    {{ $service->featured ? __('Yes') : __('No') }}
                    @if($service->featured)
                        <i class="bi bi-check-circle-fill text-success"></i>
                    @else
                        <i class="bi bi-x-circle-fill text-danger"></i>
                    @endif
                </td>
                <td>
                    <span
                        class="badge badge-light-{{ $service->status == 'Published' ? 'success' : 'warning' }} fs-7 fw-bold">{{ __($service->status) }}</span>
                </td>
                <td>
                    {{ $service->created_at->diffForHumans() }}
                </td>
                <td>
                    {{ $service->visits }}
                </td>
                <td>
                    <a href="{{ route('admin.corporate_services.edit', $service->id) }}"
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
