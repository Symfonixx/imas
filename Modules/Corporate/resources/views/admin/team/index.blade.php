@section('title', __('Team'))

@section('toolbar')
    @php
        $breadcrumbItems = [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard.index')],
            ['label' => __('Corporate'), 'url' => '#'],
            ['label' => __('Team')],
        ];
    @endphp
    <x-admin.breadcrumb :pageTitle="__('Team')" :breadcrumbItems="$breadcrumbItems"/>
    <div class="d-flex align-items-center gap-2 gap-lg-3">
        <a class="btn btn-sm fw-bold btn-primary" href="{{ route('admin.corporate_teams.create') }}">
            {{ __('Add Team Member') }}
        </a>
    </div>
@endsection
@section('js')
@endsection
<x-admin-layout>
    <x-admin.table :model="$model" :search="__('Search In Team')" :form-url="route('admin.corporate_teams.deleteMulti')">
        <thead>
        <tr class="text-start text-muted fw-bold fs-7 gs-0">
            <th class="w-10px pe-2" data-orderable="false">
                <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                    <input class="form-check-input" type="checkbox" data-kt-check="true"
                           data-kt-check-target="#dataTable .form-check-input" value="1"/>
                </div>
            </th>
            <th class="min-w-125px">{{ __('Avatar') }}</th>
            <th class="min-w-200px">{{ __('Name') }}</th>
            <th class="min-w-150px">{{ __('Position') }}</th>
            <th class="min-w-100px">{{ __('Rank') }}</th>
            <th class="min-w-150px">{{ __('Publish Status') }}</th>
            <th class="min-w-150px">{{ __('Created At') }}</th>
            <th class="min-w-100px text-end rounded-end"></th>
        </thead>
        <tbody class="text-gray-600 fw-semibold">
        @foreach($model as $member)
            <tr>
                <td>
                    <div class="form-check form-check-sm form-check-custom form-check-solid">
                        <input class="form-check-input" type="checkbox" name="ids[]" value="{{ $member->id }}"/>
                    </div>
                </td>
                <td>
                    <div class="symbol symbol-60px">
                        <img src="{{ $member->avatar_link }}" alt="" class="rounded object-fit-cover w-100 h-100"/>
                    </div>
                </td>
                <td>
                    <span class="fw-bolder text-hover-primary fs-6">{{ $member->name }}</span>
                </td>
                <td>
                    {{ $member->position ?: '—' }}
                </td>
                <td>{{ $member->rank }}</td>
                <td>
                    <span
                        class="badge badge-light-{{ $member->status == 'Published' ? 'success' : 'warning' }} fs-7 fw-bold">{{ __($member->status) }}</span>
                </td>
                <td>
                    {{ $member->created_at->diffForHumans() }}
                </td>
                <td class="text-end">
                    <a href="{{ route('admin.corporate_teams.edit', $member->id) }}"
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
