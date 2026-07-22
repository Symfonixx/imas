@section('title', __('Edit property attribute'))

@section('toolbar')
    @php
        $breadcrumbItems = [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard.index')],
            ['label' => __('Property attributes'), 'url' => route('admin.property_attributes.index')],
            ['label' => __('Edit property attribute')],
        ];
    @endphp
    <x-admin.breadcrumb :pageTitle="__('Edit property attribute')" :breadcrumbItems="$breadcrumbItems"/>
@endsection

@section('js')
    @include('property::admin.property_attribute._form_scripts')
@endsection

<x-admin-layout>
    <form method="POST" action="{{ route('admin.property_attributes.update', $attribute) }}">
        @csrf
        @method('PUT')
        @include('property::admin.property_attribute._form')
    </form>
</x-admin-layout>
