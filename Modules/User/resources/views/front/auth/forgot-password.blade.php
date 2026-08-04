@extends('layouts.front')

@section('content')
<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card imas-auth-page-card">
                <div class="card-header text-center">
                    <h3 class="text-md font-semibold mb-0">{{ front_trans('Forgot Password') }}</h3>
                </div>
                <div class="card-body text-center">
                    <p class="text-sm text-dim mb-3">{{ front_trans('auth_modal.forgot_page_opening') }}</p>
                    <button type="button" class="btn btn-primary" data-open-auth="forgot">
                        {{ front_trans('Forgot Password') }}
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
    window.dispatchEvent(new CustomEvent('imas-open-auth', { detail: { tab: 'forgot' } }));
    setTimeout(() => window.dispatchEvent(new CustomEvent('imas-open-auth', { detail: { tab: 'forgot' } })), 50);
});
</script>
@endpush
