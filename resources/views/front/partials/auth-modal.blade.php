@php
    $globals = $globals ?? [];
    $media = $globals['media'] ?? [];
    $logoUrl = $media['transparent_logo'] ?? ($media['white_logo'] ?? '');
    $countries = $globals['countries'] ?? [];
    $countriesWithPhone = array_values(array_filter(
        $countries,
        static fn ($c) => trim((string) ($c['phone_code'] ?? '')) !== '',
    ));
    if ($countriesWithPhone === []) {
        $countriesWithPhone = $countries;
    }
    $locale = $locale ?? app()->getLocale();
    $preferIso = ['tr' => 'TR', 'en' => 'US', 'ar' => 'SA'][$locale] ?? 'TR';
    $defaultCountry = null;
    foreach ($countriesWithPhone as $c) {
        if (($c['iso_code_2'] ?? '') === $preferIso) {
            $defaultCountry = $c;
            break;
        }
    }
    if ($defaultCountry === null && $countriesWithPhone !== []) {
        $defaultCountry = $countriesWithPhone[0];
    }
    $flashStatus = $flash['status'] ?? session('status');
    $resetToken = $reset_token ?? request()->route('token');
    $resetEmail = old('email', $reset_email ?? request()->query('email', ''));
@endphp
<div
    class="login-and-register-form modal imas-auth-modal"
    role="dialog"
    aria-modal="true"
    aria-label="{{ front_trans('auth_modal.dialog_label') }}"
    x-data="imasAuthModal({
        startTab: @js(request()->is('*reset-password*') ? 'reset' : (request()->is('*forgot-password*') ? 'forgot' : 'login')),
        resetToken: @js((string) ($resetToken ?? '')),
        resetEmail: @js((string) $resetEmail),
        defaultCountryId: @js($defaultCountry['id'] ?? null),
        flashStatus: @js((string) ($flashStatus ?? '')),
        loginNote: @js(front_trans('LoginNote')),
        registerNote: @js(front_trans('RegisterNote')),
        resetNote: @js(front_trans('Reset Password')),
        forgotNote: @js(front_trans('Forgot Password')),
        termsRequired: @js(front_trans('auth_modal.terms_required')),
        mobileInvalid: @js(front_trans('auth_modal.mobile_invalid_length')),
    })"
    x-show="open"
    x-cloak
    @imas-open-auth.window="openWith($event.detail?.tab || 'login')"
    style="display: none;"
    :style="open ? 'display: block !important' : 'display: none'"
>
    <div class="main-overlay" tabindex="-1" @click="close()"></div>
    <div class="main-register-holder">
        <div class="main-register fl-wrap">
            <div
                class="close-reg"
                role="button"
                tabindex="0"
                aria-label="{{ front_trans('auth_modal.close') }}"
                @click="close()"
                @keydown.enter.prevent="close()"
                @keydown.space.prevent="close()"
            >
                <i class="fa fa-times" aria-hidden="true"></i>
            </div>
            @if ($logoUrl)
                <div class="app_logo">
                    <img src="{{ $logoUrl }}" data-sticky-logo="{{ $logoUrl }}" alt="">
                </div>
            @endif
            <h3 class="text-center" x-text="noteText()"></h3>

            {{-- Forgot password --}}
            <div class="custom-form" x-show="subview === 'forgot'" x-cloak>
                <a href="#" class="imas-auth-modal__back text-start" @click.prevent="subview = null; tab = 'login'">
                    <i class="fa fa-angle-left fa-lg imas-auth-modal__back-icon" aria-hidden="true"></i>
                    <span class="imas-auth-modal__back-label">{{ front_trans('auth_modal.back_to_login') }}</span>
                </a>
                @if ($flashStatus)
                    <p class="imas-auth-modal__status" role="status">{{ $flashStatus }}</p>
                @endif
                <form method="POST" action="{{ route('password.email') }}" class="forgot-password-form">
                    @csrf
                    <div>
                        <label for="imas-auth-forgot-email">{{ front_trans('Email') }} *</label>
                        <input id="imas-auth-forgot-email" type="email" name="email" autocomplete="email" required value="{{ old('email') }}">
                        @error('email')
                            <span class="imas-auth-field-error">{{ $message }}</span>
                        @enderror
                        <button type="submit" class="log-submit-btn">
                            <span>{{ front_trans('Send Email Verification') }}</span>
                        </button>
                    </div>
                </form>
            </div>

            {{-- Login / Register / Reset --}}
            <div id="tabs-container" x-show="subview !== 'forgot'" x-cloak>
                @if ($flashStatus)
                    <p class="imas-auth-modal__status" role="status" x-show="tab === 'login'">{{ $flashStatus }}</p>
                @endif
                <ul class="tabs-menu">
                    <li :class="{ current: tab === 'login' }">
                        <a href="#tab-imas-login" @click.prevent="tab = 'login'">{{ front_trans('Login') }}</a>
                    </li>
                    <li :class="{ current: tab === 'register' }">
                        <a href="#tab-imas-register" @click.prevent="tab = 'register'">{{ front_trans('Register') }}</a>
                    </li>
                    <li :class="{ current: tab === 'reset' }" x-show="tab === 'reset' || resetToken">
                        <a href="#tab-imas-reset" @click.prevent="tab = 'reset'">{{ front_trans('Reset Password') }}</a>
                    </li>
                </ul>
                <div class="tab">
                    <div id="tab-imas-login" class="tab-contents" :class="{ 'imas-auth-tab--active': tab === 'login' }">
                        <div class="custom-form">
                            <form method="POST" action="{{ route('login.store') }}">
                                @csrf
                                <label for="imas-auth-login-email">{{ front_trans('Email') }} *</label>
                                <input id="imas-auth-login-email" type="email" name="email" autocomplete="username" required value="{{ old('email') }}">
                                @error('email')
                                    <span class="imas-auth-field-error">{{ $message }}</span>
                                @enderror
                                <label for="imas-auth-login-password">{{ front_trans('Password') }} *</label>
                                <div class="imas-auth-password-field">
                                    <input
                                        id="imas-auth-login-password"
                                        :type="showLoginPass ? 'text' : 'password'"
                                        name="password"
                                        autocomplete="current-password"
                                        required
                                    >
                                    <button type="button" class="imas-auth-password-toggle" @click="showLoginPass = !showLoginPass">
                                        <i class="fa" :class="showLoginPass ? 'fa-eye-slash' : 'fa-eye'" aria-hidden="true"></i>
                                    </button>
                                </div>
                                @error('password')
                                    <span class="imas-auth-field-error">{{ $message }}</span>
                                @enderror
                                <button type="submit" class="log-submit-btn"><span>{{ front_trans('Login') }}</span></button>
                                <div class="clearfix"></div>
                                <div class="filter-tags">
                                    <input id="imas-auth-remember" type="checkbox" name="remember" value="1" class="mx-2 remember-me-checkbox">
                                    <label for="imas-auth-remember">{{ front_trans('Remember Me') }}</label>
                                </div>
                            </form>
                            <div class="lost_password">
                                <a href="#" @click.prevent="subview = 'forgot'">{{ front_trans('Forgot Password') }}</a>
                            </div>
                        </div>
                    </div>

                    <div class="tab">
                        <div id="tab-imas-register" class="tab-contents" :class="{ 'imas-auth-tab--active': tab === 'register' }">
                            <div class="custom-form main-register-form">
                                <form method="POST" action="{{ route('register.store') }}" @submit="prepareRegister($event)">
                                    @csrf
                                    <div class="imas-auth-form-field-row">
                                        <div class="imas-auth-form-field">
                                            <label for="imas-auth-reg-first-name">{{ front_trans('contact_us.first_name') }} *</label>
                                            <input id="imas-auth-reg-first-name" type="text" name="first_name" autocomplete="given-name" required maxlength="120" value="{{ old('first_name') }}">
                                            @error('first_name')
                                                <span class="imas-auth-field-error">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="imas-auth-form-field">
                                            <label for="imas-auth-reg-last-name">{{ front_trans('contact_us.last_name') }} *</label>
                                            <input id="imas-auth-reg-last-name" type="text" name="last_name" autocomplete="family-name" required maxlength="120" value="{{ old('last_name') }}">
                                            @error('last_name')
                                                <span class="imas-auth-field-error">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="imas-auth-form-field">
                                        <label for="imas-auth-reg-email">{{ front_trans('Email') }} *</label>
                                        <input id="imas-auth-reg-email" type="email" name="email" autocomplete="email" required value="{{ old('email') }}">
                                        @error('email')
                                            <span class="imas-auth-field-error">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="imas-auth-form-field">
                                        <label for="imas-auth-reg-mobile">{{ front_trans('Mobile') }} *</label>
                                        <div class="imas-auth-phone-field" dir="ltr">
                                            <div class="imas-auth-country-select-shell">
                                                <select name="country_id" class="imas-auth-country-trigger" x-model="countryId" style="appearance:auto;padding-inline-end:0.75rem;min-width:6.75rem;">
                                                    @foreach ($countriesWithPhone as $c)
                                                        <option value="{{ $c['id'] }}" data-code="{{ preg_replace('/\D+/', '', (string) ($c['phone_code'] ?? '')) }}">
                                                            +{{ preg_replace('/\D+/', '', (string) ($c['phone_code'] ?? '')) ?: '—' }} {{ $c['iso_code_2'] ?? '' }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <span class="imas-auth-phone-sep" aria-hidden="true"></span>
                                            <input
                                                id="imas-auth-reg-mobile"
                                                type="tel"
                                                inputmode="numeric"
                                                autocomplete="tel-national"
                                                class="imas-auth-phone-input"
                                                required
                                                placeholder="{{ front_trans('auth_modal.mobile_national_placeholder') }}"
                                                x-model="mobileLocal"
                                            >
                                            <input type="hidden" name="mobile" :value="fullMobile()">
                                        </div>
                                        @error('mobile')
                                            <span class="imas-auth-field-error">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="imas-auth-form-field">
                                        <label for="imas-auth-reg-password">{{ front_trans('Password') }} *</label>
                                        <div class="imas-auth-password-field">
                                            <input id="imas-auth-reg-password" :type="showRegPass ? 'text' : 'password'" name="password" autocomplete="new-password" required>
                                            <button type="button" class="imas-auth-password-toggle" @click="showRegPass = !showRegPass">
                                                <i class="fa" :class="showRegPass ? 'fa-eye-slash' : 'fa-eye'" aria-hidden="true"></i>
                                            </button>
                                        </div>
                                        @error('password')
                                            <span class="imas-auth-field-error">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="imas-auth-form-field">
                                        <label for="imas-auth-reg-password-confirmation">{{ front_trans('Confirm Password') }} *</label>
                                        <div class="imas-auth-password-field">
                                            <input id="imas-auth-reg-password-confirmation" :type="showRegConfirm ? 'text' : 'password'" name="password_confirmation" autocomplete="new-password" required>
                                            <button type="button" class="imas-auth-password-toggle" @click="showRegConfirm = !showRegConfirm">
                                                <i class="fa" :class="showRegConfirm ? 'fa-eye-slash' : 'fa-eye'" aria-hidden="true"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="imas-auth-terms-wrap">
                                        <div class="filter-tags imas-auth-terms">
                                            <input id="imas-auth-terms" type="checkbox" class="mx-2 remember-me-checkbox" x-model="termsAccepted">
                                            <label for="imas-auth-terms" class="imas-auth-terms__label">
                                                {{ front_trans('auth_modal.agree_terms_prefix') }}
                                                <a href="#" class="imas-auth-terms__link" @click.prevent>{{ front_trans('auth_modal.terms_and_conditions') }}</a>
                                            </label>
                                        </div>
                                        <p class="imas-auth-terms__error" role="alert" x-show="termsError" x-text="termsError"></p>
                                    </div>
                                    <div class="imas-auth-form-field imas-auth-form-field--actions">
                                        <button type="submit" class="log-submit-btn"><span>{{ front_trans('Register') }}</span></button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="tab">
                        <div id="tab-imas-reset" class="tab-contents" :class="{ 'imas-auth-tab--active': tab === 'reset' }">
                            <div class="custom-form">
                                <p class="imas-auth-modal__hint" x-show="!resetToken">{{ front_trans('auth_modal.reset_hint') }}</p>
                                <form method="POST" action="{{ route('password.update') }}">
                                    @csrf
                                    <input type="hidden" name="token" :value="resetToken">
                                    <label for="imas-auth-reset-email">{{ front_trans('Email') }} *</label>
                                    <input id="imas-auth-reset-email" type="email" name="email" autocomplete="email" required :value="resetEmail">
                                    @error('email')
                                        <span class="imas-auth-field-error">{{ $message }}</span>
                                    @enderror
                                    <label for="imas-auth-reset-password">{{ front_trans('Password') }} *</label>
                                    <input id="imas-auth-reset-password" type="password" name="password" autocomplete="new-password" required>
                                    @error('password')
                                        <span class="imas-auth-field-error">{{ $message }}</span>
                                    @enderror
                                    <label for="imas-auth-reset-password-confirmation">{{ front_trans('Confirm Password') }} *</label>
                                    <input id="imas-auth-reset-password-confirmation" type="password" name="password_confirmation" autocomplete="new-password" required>
                                    <button type="submit" class="log-submit-btn" :disabled="!resetToken">
                                        <span>{{ front_trans('Reset Password') }}</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
