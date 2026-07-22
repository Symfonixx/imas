@section('title', __('Media Library'))

@section('toolbar')
    @php
        $breadcrumbItems = [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard.index')],
            ['label' => 'Media Library'],
        ];
    @endphp
    <x-admin.breadcrumb :pageTitle="__('Media Library')" :breadcrumbItems="$breadcrumbItems"/>
@endsection

<x-admin-layout>
    <div class="card card-flush">
        <div class="card-header align-items-center">
            <div class="card-title">
                <h2>{{ __('Media Library') }}</h2>
            </div>
            <div class="card-toolbar">
                <span class="text-muted">{{ __('Manage uploaded files and reuse them in content, SEO images, and editors.') }}</span>
            </div>
        </div>
        <div class="card-body">
            <x-admin.media-library-browser instance="page" :autoload="true"/>
        </div>
    </div>
</x-admin-layout>
