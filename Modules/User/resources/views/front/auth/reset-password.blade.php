@extends('layouts.front')

@section('content')
<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card imas-auth-page-card">
                <div class="card-header text-center">
                    <h3 class="text-md font-semibold mb-0">{{ front_trans('Reset Password') }}</h3>
                </div>
                <div class="card-body text-center">
                    <p class="text-sm text-dim mb-3">{{ front_trans('auth_modal.reset_page_opening') }}</p>
                    <button type="button" class="btn btn-primary" data-open-auth="reset">
                        {{ front_trans('Reset Password') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    window.dispatchEvent(new CustomEvent('imas-open-auth', { detail: { tab: 'reset' } }));
    setTimeout(() => window.dispatchEvent(new CustomEvent('imas-open-auth', { detail: { tab: 'reset' } })), 50);
});
</script>
@endpush
