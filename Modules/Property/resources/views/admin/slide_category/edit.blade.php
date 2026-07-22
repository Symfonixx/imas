@section('title', __('Edit slide category'))

@section('toolbar')
    @php
        $breadcrumbItems = [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard.index')],
            ['label' => __('Slide categories'), 'url' => route('admin.slide_categories.index')],
            ['label' => __('Edit slide category')],
        ];
    @endphp
    <x-admin.breadcrumb :pageTitle="__('Edit slide category')" :breadcrumbItems="$breadcrumbItems"/>
@endsection

<x-admin-layout>
    <form method="POST"
          action="{{ route('admin.slide_categories.update', $slideCategory) }}">
        @csrf
        @method('PUT')
        @include('property::admin.slide_category._form')
    </form>
</x-admin-layout>
