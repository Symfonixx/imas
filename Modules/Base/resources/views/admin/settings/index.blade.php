@section('title' , __('Website Configurations'))

@section('toolbar')
    @php
        $breadcrumbItems = [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard.index')],
            ['label' => 'Website Configurations'],
        ];
    @endphp
    <x-admin.breadcrumb :pageTitle="__('Website Configurations')" :breadcrumbItems="$breadcrumbItems"/>
    <div class="d-flex align-items-center gap-2 gap-lg-3"></div>
@endsection

<x-admin-layout>
    <x-admin.create-card title="Website Configurations" :formUrl="route('admin.settings.store')">
        <div class="row mb-10">
            <!--begin::Col-->
            <div class="col-xl-3 mb-5">
                <div class="fs-6 fw-bold mt-2 mb-5">{{__('Transparent Logo')}}</div>
                <x-admin.image-input
                    name="imgs[white_logo]"
                    accept="image/*"
                    :preview="asset('storage/' . $settings->get('white_logo' ,'default.jpg'))"
                    :mediaPath="$settings->get('white_logo')"
                    mediaInputName="imgs_media[white_logo]"/>
                <!--begin::Hint-->
                <div class="form-text"> 75px * 150px</div>
                <!--end::Hint-->
            </div>
            <!--end::Col-->

            <!--begin::Col-->
            <div class="col-xl-3 mb-5">
                <div class="fs-6 fw-bold mt-2 mb-5">{{__('Dark Logo')}}</div>
                <x-admin.image-input
                    name="imgs[black_logo]"
                    accept="image/*"
                    :preview="asset('storage/' . $settings->get('black_logo' ,'default.jpg'))"
                    :mediaPath="$settings->get('black_logo')"
                    mediaInputName="imgs_media[black_logo]"/>
                <!--begin::Hint-->
                <div class="form-text"> 238px * 51px</div>
                <!--end::Hint-->
            </div>
            <!--end::Col-->
            <!--begin::Col-->
            <div class="col-xl-3 mb-5">
                <div class="fs-6 fw-bold mt-2 mb-5">{{ __('Admin Logo') }}</div>
                <x-admin.image-input
                    name="imgs[admin_logo]"
                    accept="image/*"
                    :preview="$settings->get('admin_logo') ? asset('storage/' . $settings->get('admin_logo')) : asset('images/logo.png')"
                    :mediaPath="$settings->get('admin_logo')"
                    mediaInputName="imgs_media[admin_logo]"/>
                <div class="form-text">{{ __('Used in the admin panel sidebar navigation.') }}</div>
            </div>
            <!--end::Col-->
            <!--begin::Col-->
            <div class="col-xl-3 mb-5">
                <div class="fs-6 fw-bold mt-2 mb-5">{{__('Meta Image')}}</div>
                <x-admin.image-input
                    name="imgs[meta_img]"
                    accept="image/*"
                    :preview="asset('storage/' . $settings->get('meta_img' ,'default.jpg'))"
                    :mediaPath="$settings->get('meta_img')"
                    mediaInputName="imgs_media[meta_img]"/>
                <!--begin::Hint-->
                <div class="form-text"> 600px * 600px</div>
                <!--end::Hint-->
            </div>
            <!--end::Col-->

        </div>

        <h5 class="my-3 fw-bold text-primary">{{ __('Pages Banners') }}</h5>
        <hr/>
        <div class="row mb-10">
            <div class="col-xl-3 mb-5">
                <div class="fs-6 fw-bold mt-2 mb-5">{{ __('Contact Us Page Banner') }}</div>
                <x-admin.image-input
                    name="imgs[contact_us_banner]"
                    accept="image/*"
                    :preview="asset('storage/' . $settings->get('contact_us_banner', 'default.jpg'))"
                    :mediaPath="$settings->get('contact_us_banner')"
                    mediaInputName="imgs_media[contact_us_banner]"/>
                <div class="form-text">{{ __('Minimum 1920×600 px, max 4 MB (any supported image format).') }}</div>
            </div>
            <div class="col-xl-3 mb-5">
                <div class="fs-6 fw-bold mt-2 mb-5">{{ __('Blog Details Page Banner') }}</div>
                <x-admin.image-input
                    name="imgs[blog_show_banner]"
                    accept="image/*"
                    :preview="asset('storage/' . $settings->get('blog_show_banner', 'default.jpg'))"
                    :mediaPath="$settings->get('blog_show_banner')"
                    mediaInputName="imgs_media[blog_show_banner]"/>
                <div class="form-text">{{ __('Minimum 1920×600 px, max 4 MB (any supported image format).') }}</div>
            </div>
            <div class="col-xl-3 mb-5">
                <div class="fs-6 fw-bold mt-2 mb-5">{{ __('Property Listings Page Banner') }}</div>
                <x-admin.image-input
                    name="imgs[property_show_banner]"
                    accept="image/*"
                    :preview="asset('storage/' . $settings->get('property_show_banner', 'default.jpg'))"
                    :mediaPath="$settings->get('property_show_banner')"
                    mediaInputName="imgs_media[property_show_banner]"/>
                <div class="form-text">{{ __('Minimum 1920×600 px, max 4 MB (any supported image format).') }}</div>
            </div>
        </div>
        <div class="row mb-8">
            <!--begin::Col-->
            <div class="col-xl-3">
                <div class="fs-6 fw-bold mt-2 mb-3"><i
                        class="bi bi-phone mx-1 text-primary"></i> {{__('Website Phone')}}</div>
            </div>
            <!--end::Col-->
            <!--begin::Col-->
            <div class="col-xl-9 fv-row">
                <input type="text" class="form-control form-control-solid" name="data[phone]"
                       value="{{$settings->get('phone')}}" placeholder="00905234***"/>
            </div>
        </div>

        <div class="row mb-8">
            <!--begin::Col-->
            <div class="col-xl-3">
                <div class="fs-6 fw-bold mt-2 mb-3"><i
                        class="bi bi-envelope mx-1 text-primary"></i> {{__('Website Email')}}</div>
            </div>
            <!--end::Col-->
            <!--begin::Col-->
            <div class="col-xl-9 fv-row">
                <input type="text" class="form-control form-control-solid" name="data[email]"
                       value="{{$settings->get('email')}}" placeholder="support@example.com"/>
            </div>
        </div>

        <div class="row mb-8">
            <!--begin::Col-->
            <div class="col-xl-3">
                <div class="fs-6 fw-bold mt-2 mb-3"><i
                        class="bi bi-geo-fill mx-1 text-primary"></i> {{__('Website Address')}}</div>
            </div>
            <!--end::Col-->
            <!--begin::Col-->
            <div class="col-xl-9 fv-row">
                <input type="text" class="form-control form-control-solid" name="data[address]"
                       value="{{$settings->get('address')}}" placeholder="California, TX 70240"/>
            </div>
        </div>

{{--        <div class="row mb-8">--}}
{{--            <!--begin::Col-->--}}
{{--            <div class="col-xl-3">--}}
{{--                <div class="fs-6 fw-bold mt-2 mb-3"><i--}}
{{--                        class="bi bi-map mx-1 text-primary"></i> {{__('Website Map')}}</div>--}}
{{--            </div>--}}
{{--            <!--end::Col-->--}}
{{--            <!--begin::Col-->--}}
{{--            <div class="col-xl-9 fv-row">--}}
{{--                <textarea name="data[map]"--}}
{{--                          class="form-control form-control-solid h-150px">{{$settings->get('map')}}</textarea>--}}
{{--            </div>--}}
{{--            <!--begin::Col-->--}}
{{--        </div>--}}


          <div class="row mb-8">
            <!--begin::Col-->
            <div class="col-xl-3">
                <div class="fs-6 fw-bold mt-2 mb-3"><i
                        class="bi bi-code mx-1 text-primary"></i> {{__('Header Scripts')}}</div>
                <div class="text-muted fs-7">{{__('Paste the Google Tag Manager <script> snippet here once. Scripts load after the page finishes painting (faster PageSpeed). Do not also paste the same GTM snippet in Footer Scripts.')}}</div>
            </div>
            <!--end::Col-->
            <!--begin::Col-->
            <div class="col-xl-9 fv-row">
                <textarea name="data[header_scripts]"
                          class="form-control form-control-solid h-150px js-settings-html-field"
                          spellcheck="false">{{$settings->get('header_scripts')}}</textarea>
            </div>
            <!--begin::Col-->
        </div>

          <div class="row mb-8">
            <!--begin::Col-->
            <div class="col-xl-3">
                <div class="fs-6 fw-bold mt-2 mb-3"><i
                        class="bi bi-code mx-1 text-primary"></i> {{__('Body Scripts')}}</div>
                <div class="text-muted fs-7">{{__('Prefer the GTM noscript iframe here. Any script tags are deferred until after first paint (same as Header/Footer Scripts).')}}</div>
            </div>
            <!--end::Col-->
            <!--begin::Col-->
            <div class="col-xl-9 fv-row">
                <textarea name="data[body_scripts]"
                          class="form-control form-control-solid h-150px js-settings-html-field"
                          spellcheck="false">{{$settings->get('body_scripts')}}</textarea>
            </div>
            <!--begin::Col-->
        </div>

           <div class="row mb-8">
            <!--begin::Col-->
            <div class="col-xl-3">
                <div class="fs-6 fw-bold mt-2 mb-3"><i
                        class="bi bi-code mx-1 text-primary"></i> {{__('Footer Scripts')}}</div>
                <div class="text-muted fs-7">{{__('Extra pixels/chat widgets only. Do not duplicate Google Tag Manager here if it is already in Header Scripts — double-loading slows the site and skews analytics.')}}</div>
            </div>
            <!--end::Col-->
            <!--begin::Col-->
            <div class="col-xl-9 fv-row">
                <textarea name="data[footer_scripts]"
                          class="form-control form-control-solid h-150px js-settings-html-field"
                          spellcheck="false">{{$settings->get('footer_scripts')}}</textarea>
            </div>
            <!--begin::Col-->
        </div>


        <h5 class="my-3 fw-bold text-primary">{{__('Social Media')}}</h5>
        <hr/>
        <div class="row mb-8">
            <!--begin::Col-->
            <div class="col-xl-3">
                <div class="fs-6 fw-bold mt-2 mb-3"><i
                        class="bi bi-whatsapp mx-1 text-success"></i> {{__('Whatsapp')}}</div>
            </div>
            <!--end::Col-->
            <!--begin::Col-->
            <div class="col-xl-9 fv-row">
                <input type="text" class="form-control form-control-solid" name="data[whatsapp]"
                       value="{{$settings->get('whatsapp')}}" placeholder="90564xxxxxxx"/>
            </div>
        </div>
        <div class="row mb-8">
            <!--begin::Col-->
            <div class="col-xl-3">
                <div class="fs-6 fw-bold mt-2 mb-3"><i
                        class="bi bi-facebook mx-1 text-primary"></i> {{__('Facebook')}}</div>
            </div>
            <!--end::Col-->
            <!--begin::Col-->
            <div class="col-xl-9 fv-row">
                <input type="text" class="form-control form-control-solid" name="data[facebook]"
                       value="{{$settings->get('facebook')}}" placeholder="https://www.facebook.com/xxxx"/>
            </div>
        </div>
        <div class="row mb-8">
            <!--begin::Col-->
            <div class="col-xl-3">
                <div class="fs-6 fw-bold mt-2 mb-3"><i
                        class="bi bi-instagram mx-1 text-danger"></i> {{__('Instagram')}}</div>
            </div>
            <!--end::Col-->
            <!--begin::Col-->
            <div class="col-xl-9 fv-row">
                <input type="text" class="form-control form-control-solid" name="data[instagram]"
                       value="{{$settings->get('instagram')}}" placeholder="https://www.instagram.com/xxxx"/>
            </div>
        </div>
        <div class="row mb-8">
            <!--begin::Col-->
            <div class="col-xl-3">
                <div class="fs-6 fw-bold mt-2 mb-3"><i class="bi bi-youtube mx-1 text-danger"></i> {{__('Youtube')}}
                </div>
            </div>
            <!--end::Col-->
            <!--begin::Col-->
            <div class="col-xl-9 fv-row">
                <input type="text" class="form-control form-control-solid" name="data[youtube]"
                       value="{{$settings->get('youtube')}}" placeholder="https://www.youtube.com/xxxx"/>
            </div>
        </div>
        <div class="row mb-8">
            <!--begin::Col-->
            <div class="col-xl-3">
                <div class="fs-6 fw-bold mt-2 mb-3"><i
                        class="bi bi-twitter mx-1 text-primary"></i> {{__('Twitter')}}</div>
            </div>
            <!--end::Col-->
            <!--begin::Col-->
            <div class="col-xl-9 fv-row">
                <input type="text" class="form-control form-control-solid" name="data[twitter]"
                       value="{{$settings->get('twitter')}}" placeholder="https://www.twitter.com/xxxx"/>
            </div>
        </div>
        <div class="row mb-8">
            <!--begin::Col-->
            <div class="col-xl-3">
                <div class="fs-6 fw-bold mt-2 mb-3"><i
                        class="fab fa-tiktok mx-1 text-dark"></i> {{__('TikTok')}}</div>
            </div>
            <!--end::Col-->
            <!--begin::Col-->
            <div class="col-xl-9 fv-row">
                <input type="text" class="form-control form-control-solid" name="data[tiktok]"
                       value="{{$settings->get('tiktok')}}" placeholder="https://www.tiktok.com/@xxxx"/>
            </div>
        </div>


    </x-admin.create-card>

    @push('scripts')
        <script>
            (function () {
                var PREFIX = 'b64:';

                function utf8ToBase64(str) {
                    return btoa(unescape(encodeURIComponent(str)));
                }

                var form = document.querySelector('.imas-admin-form-card form');
                if (!form) {
                    return;
                }

                form.addEventListener('submit', function () {
                    form.querySelectorAll('.js-settings-html-field').forEach(function (el) {
                        var raw = el.value || '';
                        if (raw === '' || raw.indexOf(PREFIX) === 0) {
                            return;
                        }
                        el.value = PREFIX + utf8ToBase64(raw);
                    });
                });
            })();
        </script>
    @endpush
</x-admin-layout>
