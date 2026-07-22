@once
    {{-- Shared browser JS/CSS are loaded via the browser component --}}
@endonce

<div class="modal fade" id="mediaLibraryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">{{ __('Media Library') }}</h3>
                <button type="button" class="btn btn-icon btn-sm btn-active-light-primary" data-bs-dismiss="modal" aria-label="{{ __('Close') }}">
                    <i class="bi bi-x fs-2"></i>
                </button>
            </div>
            <div class="modal-body">
                <x-admin.media-library-browser instance="modal" :autoload="false"/>
            </div>
        </div>
    </div>
</div>
