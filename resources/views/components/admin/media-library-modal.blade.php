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

{{-- Metronic-style folder create/edit modal (shared across media library instances) --}}
<div class="modal fade" id="kt_modal_media_folder" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header" id="kt_modal_media_folder_header">
                <h2 class="fw-bolder" data-ml-folder-modal-title>{{ __('Edit folder') }}</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-ml-folder-modal-action="close">
                    <span class="svg-icon svg-icon-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1"
                                  transform="rotate(-45 6 17.3137)" fill="black"/>
                            <rect x="7.41422" y="6" width="16" height="2" rx="1" transform="rotate(45 7.41422 6)"
                                  fill="black"/>
                        </svg>
                    </span>
                </div>
            </div>
            <div class="modal-body scroll-y mx-5 my-7">
                <form id="kt_modal_media_folder_form" class="form" action="#">
                    <div class="d-flex flex-column">
                        <div class="fv-row mb-7">
                            <label class="fs-6 fw-bold form-label mb-2">
                                <span class="required">{{ __('Folder name') }}</span>
                            </label>
                            <input type="text"
                                   name="name"
                                   class="form-control form-control-solid"
                                   maxlength="120"
                                   data-ml-folder-modal-name
                                   placeholder="{{ __('Enter folder name') }}"
                                   required>
                            <div class="invalid-feedback" data-ml-folder-modal-name-error></div>
                        </div>
                        <div class="fv-row mb-7">
                            <label class="fs-6 fw-bold form-label mb-2">{{ __('Parent folder') }}</label>
                            <select name="parent_id"
                                    class="form-select form-select-solid"
                                    data-ml-folder-modal-parent>
                                <option value="">{{ __('Root') }}</option>
                            </select>
                            <div class="invalid-feedback d-block" data-ml-folder-modal-parent-error></div>
                            <div class="form-text text-muted">{{ __('Choose a parent to nest this folder in the tree.') }}</div>
                        </div>
                    </div>
                    <div class="text-center pt-5">
                        <button type="button" class="btn btn-light me-3" data-ml-folder-modal-action="cancel">
                            {{ __('Discard') }}
                        </button>
                        <button type="submit" class="btn btn-primary" data-ml-folder-modal-action="submit">
                            <span class="indicator-label">{{ __('Submit') }}</span>
                            <span class="indicator-progress">
                                {{ __('Please Wait') }}
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
