@section('title', __('Add property'))

@section('js')
    @include('base::shared._tinymce', [
        'tinymceSelector' => '#tinymce-overview, #tinymce-why-to-buy, #tinymce-facilities, #tinymce-content',
        'tinymceHeight' => 400,
    ])
@endsection

@section('toolbar')
    @php
        $breadcrumbItems = [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard.index')],
            ['label' => __('Properties'), 'url' => route('admin.properties.index')],
            ['label' => __('Add property')],
        ];
    @endphp
    <x-admin.breadcrumb :pageTitle="__('Add property')" :breadcrumbItems="$breadcrumbItems"/>
@endsection

@include('cms::admin.partials._seo_assets')

<x-admin-layout>
    <form method="POST" action="{{ route('admin.properties.store') }}" enctype="multipart/form-data" id="property-form">
        @csrf
        @include('property::admin.property.partials._form')
    </form>
</x-admin-layout>
