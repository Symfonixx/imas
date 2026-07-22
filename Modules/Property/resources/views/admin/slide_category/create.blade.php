@section('title', __('Add slide category'))

@section('toolbar')
    @php
        $breadcrumbItems = [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard.index')],
            ['label' => __('Slide categories'), 'url' => route('admin.slide_categories.index')],
            ['label' => __('Add slide category')],
        ];
    @endphp
    <x-admin.breadcrumb :pageTitle="__('Add slide category')" :breadcrumbItems="$breadcrumbItems"/>
@endsection

<x-admin-layout>
    <form method="POST"
          action="{{ route('admin.slide_categories.store') }}">
        @csrf
        @include('property::admin.slide_category._form')
    </form>
</x-admin-layout>
