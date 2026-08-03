@php
    $action = $action ?? ($contactStoreUrl ?? route('support.contact-us.store'));
    $sourcePage = $sourcePage ?? '';
    $defaultSubject = $defaultSubject ?? '';
    $defaultMessage = $defaultMessage ?? '';
    $hideTitle = (bool) ($hideTitle ?? false);
    $hideSubject = (bool) ($hideSubject ?? false);
    $variant = $variant ?? 'page';
@endphp
<div>
    @unless ($hideTitle)
        <h3 class="imas-contact-page__heading text-xl font-semibold mb-4 text-start">
            {{ front_trans('contact_us.title') }}
        </h3>
    @endunless

    @if (! empty($flash['contact_sent']) || session('contact_sent'))
        <div class="alert alert-success imas-contact-page__alert imas-contact-page__alert--success text-start" role="status">
            {{ front_trans('contact_us.message_sent') }}
        </div>
    @endif

    <form
        class="contact-form imas-contact-form {{ $variant === 'sidebar' ? 'imas-contact-form--sidebar' : '' }}"
        method="POST"
        action="{{ $action }}"
    >
        @csrf
        <input type="hidden" name="source_url" value="{{ url()->current() }}">
        <input type="hidden" name="source_page" value="{{ $sourcePage }}">

        <div class="imas-contact-form__pair">
            <div class="imas-contact-form__pair-field">
                <div class="form-group">
                    <input
                        type="text"
                        name="first_name"
                        required
                        maxlength="120"
                        class="form-control input-custom input-full {{ $errors->has('first_name') ? 'is-invalid' : '' }}"
                        placeholder="{{ front_trans('contact_us.first_name') }}"
                        autocomplete="given-name"
                        value="{{ old('first_name') }}"
                    >
                    @error('first_name')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="imas-contact-form__pair-field">
                <div class="form-group">
                    <input
                        type="text"
                        name="last_name"
                        required
                        maxlength="120"
                        class="form-control input-custom input-full {{ $errors->has('last_name') ? 'is-invalid' : '' }}"
                        placeholder="{{ front_trans('contact_us.last_name') }}"
                        autocomplete="family-name"
                        value="{{ old('last_name') }}"
                    >
                    @error('last_name')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="imas-contact-form__pair">
            <div class="imas-contact-form__pair-field">
                <div class="form-group">
                    <input
                        type="email"
                        name="email"
                        required
                        maxlength="255"
                        class="form-control input-custom input-full {{ $errors->has('email') ? 'is-invalid' : '' }}"
                        placeholder="{{ front_trans('contact_us.email') }}"
                        autocomplete="email"
                        value="{{ old('email') }}"
                    >
                    @error('email')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="imas-contact-form__pair-field">
                <div class="form-group">
                    <input
                        type="tel"
                        name="mobile"
                        class="form-control input-custom input-full phon_num_input {{ $errors->has('mobile') ? 'is-invalid' : '' }}"
                        placeholder="{{ front_trans('auth_modal.mobile_national_placeholder') }}"
                        autocomplete="tel"
                        value="{{ old('mobile') }}"
                    >
                    @error('mobile')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        @unless ($hideSubject)
            <div class="form-group">
                <input
                    type="text"
                    name="subject"
                    class="form-control input-custom input-full {{ $errors->has('subject') ? 'is-invalid' : '' }}"
                    placeholder="{{ front_trans('contact_us.subject_optional') }}"
                    value="{{ old('subject', $defaultSubject) }}"
                >
                @error('subject')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        @else
            <input type="hidden" name="subject" value="{{ old('subject', $defaultSubject) }}">
        @endunless

        <div class="form-group">
            <textarea
                name="message"
                class="form-control textarea-custom input-full {{ $errors->has('message') ? 'is-invalid' : '' }}"
                rows="{{ $variant === 'sidebar' ? 4 : 6 }}"
                required
                maxlength="5000"
                placeholder="{{ front_trans('contact_us.message') }}"
            >{{ old('message', $defaultMessage) }}</textarea>
            @error('message')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex justify-content-start">
            <button type="submit" class="btn btn-primary imas-contact-page__submit">
                {{ front_trans('contact_us.send_message') }}
            </button>
        </div>
    </form>
</div>
