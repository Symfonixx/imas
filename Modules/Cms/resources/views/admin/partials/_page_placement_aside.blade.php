@props([
    'addToNav' => false,
    'addToFooter' => false,
    'addToTopBar' => false,
    'addToBottomBar' => false,
])

<div class="card card-flush mb-7">
    <div class="card-header">
        <div class="card-title">
            <h2 class="d-flex align-items-center">
                <i class="bi bi-layout-text-sidebar-reverse text-primary fs-3 me-2"></i>
                {{ __('Menu placement') }}
            </h2>
        </div>
    </div>
    <div class="card-body pt-3">
        <p class="text-muted fs-7 mb-5">
            {{ __('Choose where this page should appear in site navigation.') }}
        </p>

        <x-admin.toggle-switch
            name="add_to_nav"
            label="Main navigation"
            helper="Show a link in the primary site menu."
            icon="bi bi-list"
            tone="primary"
            :checked="$addToNav"
        />

        <x-admin.toggle-switch
            name="add_to_footer"
            label="Footer"
            helper="Show a link in the footer menu."
            icon="bi bi-layout-three-columns"
            tone="info"
            :checked="$addToFooter"
        />

        <x-admin.toggle-switch
            name="add_to_top_bar"
            label="Top bar"
            helper="Show a link in the top utility bar."
            icon="bi bi-window"
            tone="success"
            :checked="$addToTopBar"
        />

        <x-admin.toggle-switch
            name="add_to_bottom_bar"
            label="Bottom bar"
            helper="Show a link in the bottom bar area."
            icon="bi bi-layout-text-window-reverse"
            tone="warning"
            :checked="$addToBottomBar"
            last
        />
    </div>
</div>
