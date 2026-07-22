@props([
    'title',
    'formUrl',
    'discardUrl' => null,
    'submitLabel' => 'Save Changes',
])

<div class="card card-flush imas-admin-form-card">
    <div class="card-header">
        <div class="card-title fs-3 fw-bold">{{ __($title) }}</div>
    </div>

    <form method="POST" action="{{ $formUrl }}" enctype="multipart/form-data">
        @csrf
        <div class="card-body">
            {{ $slot }}
        </div>

        <div class="card-footer imas-admin-form-card__footer">
            <div class="imas-admin-form-actions__meta">
                <span class="imas-admin-form-actions__dot" aria-hidden="true"></span>
                <span class="imas-admin-form-actions__hint">{{ __('Review your changes before saving.') }}</span>
            </div>
            <div class="imas-admin-form-actions__btns">
                <a href="{{ $discardUrl ?? url()->previous() }}"
                   class="btn btn-light btn-active-light-primary imas-admin-form-actions__discard">
                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                    <span>{{ __('Discard') }}</span>
                </a>
                <button type="submit" class="btn btn-primary imas-admin-form-actions__save" id="submit">
                    <i class="bi bi-check2-circle" aria-hidden="true"></i>
                    <span>{{ __($submitLabel) }}</span>
                </button>
            </div>
        </div>
    </form>
</div>
