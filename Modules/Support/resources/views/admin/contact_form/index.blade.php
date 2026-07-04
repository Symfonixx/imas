@section('title' , __('Leads'))

@section('toolbar')
    @php
        $breadcrumbItems = [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard.index')],
            ['label' => 'Leads'],
        ];
    @endphp
    <x-admin.breadcrumb :pageTitle="__('Leads')" :breadcrumbItems="$breadcrumbItems"/>
    <div class="d-flex align-items-center gap-2 gap-lg-3"></div>
@endsection
@section('js')

@endsection
<x-admin-layout>
    <x-admin.table :model="$model" search="Search In Leads"
                   :formUrl="route('admin.contact_forms.deleteMulti')">
        <!--begin::Table head-->
        <thead>
        <tr class="text-start text-muted fw-bold fs-7 gs-0">
            <th class="w-10px pe-2" data-orderable="false">
                <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                    <input class="form-check-input" type="checkbox" data-kt-check="true"
                           data-kt-check-target="#dataTable .form-check-input" value="1"/>
                </div>
            </th>

            <th>{{ __('ID') }}</th>
            <th>{{ __('Name') }}</th>
            <th>{{ __('Phone') }}</th>
            <th>{{ __('URL') }}</th>
            <th>{{ __('Project / Page') }}</th>
            <th>{{ __('Message') }}</th>
            <th>{{ __('Date / Time') }}</th>
        </tr>
        </thead>
        <!--end::Table head-->
        <!--begin::Table body-->
        <tbody class="text-gray-600 fw-semibold">
        @foreach($model as $lead)
            <tr>
                <td>
                    <div class="form-check form-check-sm form-check-custom form-check-solid">
                        <input class="form-check-input" type="checkbox" name="ids[]" value="{{ $lead->id }}"/>
                    </div>
                </td>

                <td>{{ $lead->id }}</td>

                <td>
                    <span class="text-gray-800">{{ $lead->name }}</span>
                </td>

                <td>
                    @if($lead->mobile)
                        <a class="text-hover-primary text-gray-600" target="_blank"
                           href="tel:{{ $lead->mobile }}">{{ $lead->mobile }}</a>
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>

                <td class="text-break" style="max-width: 220px;">
                    @if($lead->display_source_url)
                        <a class="text-hover-primary text-gray-600" target="_blank" rel="noopener noreferrer"
                           href="{{ $lead->display_source_url }}">{{ $lead->display_source_url }}</a>
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>

                <td>
                    {{ $lead->display_source_page ?: '—' }}
                </td>

                <td class="text-break" style="max-width: 280px;">
                    {{ $lead->message }}
                </td>

                <td class="text-nowrap">
                    {{ $lead->created_at?->format('Y-m-d H:i') }}
                </td>
            </tr>
        @endforeach
        </tbody>
        <!--end::Table body-->
    </x-admin.table>
    <!--end::Card-->
</x-admin-layout>
