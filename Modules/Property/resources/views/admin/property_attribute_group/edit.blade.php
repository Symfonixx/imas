@section('title', __('Edit attribute group'))

@section('toolbar')
    @php
        $breadcrumbItems = [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard.index')],
            ['label' => __('Attribute groups'), 'url' => route('admin.property_attribute_groups.index')],
            ['label' => __('Edit attribute group')],
        ];
    @endphp
    <x-admin.breadcrumb :pageTitle="__('Edit attribute group')" :breadcrumbItems="$breadcrumbItems"/>
@endsection

<x-admin-layout>
    <form method="POST" action="{{ route('admin.property_attribute_groups.update', $group) }}">
        @csrf
        @method('PUT')
        @include('property::admin.property_attribute_group._form')
    </form>
</x-admin-layout>
