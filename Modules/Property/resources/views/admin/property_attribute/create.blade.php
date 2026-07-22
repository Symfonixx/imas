@section('title', __('Add property attribute'))

@section('toolbar')
    @php
        $breadcrumbItems = [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard.index')],
            ['label' => __('Property attributes'), 'url' => route('admin.property_attributes.index')],
            ['label' => __('Add property attribute')],
        ];
    @endphp
    <x-admin.breadcrumb :pageTitle="__('Add property attribute')" :breadcrumbItems="$breadcrumbItems"/>
@endsection

@section('js')
    @include('property::admin.property_attribute._form_scripts')
@endsection

<x-admin-layout>
    <form method="POST" action="{{ route('admin.property_attributes.store') }}">
        @csrf
        @include('property::admin.property_attribute._form')
    </form>
</x-admin-layout>
