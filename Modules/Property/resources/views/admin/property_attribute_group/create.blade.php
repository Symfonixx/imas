@section('title', __('Add attribute group'))

@section('toolbar')
    @php
        $breadcrumbItems = [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard.index')],
            ['label' => __('Attribute groups'), 'url' => route('admin.property_attribute_groups.index')],
            ['label' => __('Add attribute group')],
        ];
    @endphp
    <x-admin.breadcrumb :pageTitle="__('Add attribute group')" :breadcrumbItems="$breadcrumbItems"/>
@endsection

<x-admin-layout>
    <form method="POST" action="{{ route('admin.property_attribute_groups.store') }}">
        @csrf
        @include('property::admin.property_attribute_group._form')
    </form>
</x-admin-layout>
