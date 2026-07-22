@php
    $quickActions = array_values(array_filter([
        auth()->user()?->can('Property Management') ? [
            'label' => __('Add property'),
            'url' => route('admin.properties.create'),
            'icon' => 'bi-buildings',
            'tone' => 'navy',
        ] : null,
        auth()->user()?->can('CMS Management') ? [
            'label' => __('Add blog'),
            'url' => route('admin.blogs.create'),
            'icon' => 'bi-journal-richtext',
            'tone' => 'gold',
        ] : null,
        auth()->user()?->can('CMS Management') ? [
            'label' => __('Add page'),
            'url' => route('admin.pages.create'),
            'icon' => 'bi-file-earmark-text',
            'tone' => 'navy',
        ] : null,
        auth()->user()?->can('CMS Management') ? [
            'label' => __('Add slider'),
            'url' => route('admin.slides.create'),
            'icon' => 'bi-images',
            'tone' => 'gold',
        ] : null,
        auth()->user()?->can('CMS Management') ? [
            'label' => __('Add FAQ'),
            'url' => route('admin.faqs.create'),
            'icon' => 'bi-question-circle',
            'tone' => 'navy',
        ] : null,
        auth()->user()?->can('Media Library Management') ? [
            'label' => __('Media Library'),
            'url' => route('admin.media_library.index'),
            'icon' => 'bi-folder2-open',
            'tone' => 'gold',
        ] : null,
        auth()->user()?->can('Property Management') ? [
            'label' => __('Add location'),
            'url' => route('admin.locations.create'),
            'icon' => 'bi-geo-alt',
            'tone' => 'navy',
        ] : null,
        auth()->user()?->can('Corporate Management') ? [
            'label' => __('Add team member'),
            'url' => route('admin.corporate_teams.create'),
            'icon' => 'bi-people',
            'tone' => 'gold',
        ] : null,
        auth()->user()?->can('Corporate Management') ? [
            'label' => __('Add service'),
            'url' => route('admin.corporate_services.create'),
            'icon' => 'bi-briefcase',
            'tone' => 'navy',
        ] : null,
        auth()->user()?->can('Support Management') ? [
            'label' => __('View leads'),
            'url' => route('admin.contact_forms.index'),
            'icon' => 'bi-inbox',
            'tone' => 'gold',
        ] : null,
    ]));
@endphp

@if(count($quickActions) > 0)
    <div class="imas-admin-fab" id="imas_admin_fab" data-imas-fab>
        <div class="imas-admin-fab__rail" id="imas_admin_fab_menu" role="menu" aria-label="{{ __('Quick actions') }}">
            <div class="imas-admin-fab__title">{{ __('Quick actions') }}</div>
            @foreach($quickActions as $index => $action)
                <a href="{{ $action['url'] }}"
                   class="imas-admin-fab__item imas-admin-fab__item--{{ $action['tone'] }}"
                   role="menuitem"
                   style="--imas-fab-i: {{ $index }}"
                   title="{{ $action['label'] }}">
                    <span class="imas-admin-fab__item-icon" aria-hidden="true">
                        <i class="bi {{ $action['icon'] }}"></i>
                    </span>
                    <span class="imas-admin-fab__item-label">{{ $action['label'] }}</span>
                </a>
            @endforeach
        </div>

        <button type="button"
                class="imas-admin-fab__trigger"
                aria-expanded="false"
                aria-controls="imas_admin_fab_menu"
                aria-label="{{ __('Quick actions') }}"
                data-imas-fab-trigger>
            <i class="bi bi-plus-lg imas-admin-fab__trigger-plus" aria-hidden="true"></i>
            <i class="bi bi-x-lg imas-admin-fab__trigger-close" aria-hidden="true"></i>
        </button>
    </div>
@endif
