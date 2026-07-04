<?php

namespace Modules\Base\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $pageBannerRules = [
            'nullable',
            'image',
            'mimes:jpeg,png,jpg,webp',
            'max:4096',
            'dimensions:min_width=1920,min_height=600',
        ];

        return [
            'imgs.white_logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'imgs.black_logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'imgs.admin_logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'imgs.meta_img' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096', 'dimensions:min_width=600,min_height=600'],
            'imgs.contact_us_banner' => $pageBannerRules,
            'imgs.blog_show_banner' => $pageBannerRules,
            'imgs.property_show_banner' => $pageBannerRules,
            'data.phone' => ['nullable', 'string', 'max:50'],
            'data.email' => ['nullable', 'string', 'email', 'max:255'],
            'data.address' => ['nullable', 'string', 'max:500'],
            'data.map' => ['nullable', 'string', 'max:10000'],
            'data.whatsapp' => ['nullable', 'string', 'max:255'],
            'data.facebook' => ['nullable', 'string', 'max:255'],
            'data.instagram' => ['nullable', 'string', 'max:255'],
            'data.youtube' => ['nullable', 'string', 'max:255'],
            'data.twitter' => ['nullable', 'string', 'max:255'],
            'data.tiktok' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'imgs.contact_us_banner.dimensions' => __('The contact us banner must be at least 1920×600 pixels.'),
            'imgs.contact_us_banner.max' => __('The contact us banner may not be greater than 4 MB.'),
            'imgs.blog_show_banner.dimensions' => __('The blog details banner must be at least 1920×600 pixels.'),
            'imgs.blog_show_banner.max' => __('The blog details banner may not be greater than 4 MB.'),
            'imgs.property_show_banner.dimensions' => __('The property listings banner must be at least 1920×600 pixels.'),
            'imgs.property_show_banner.max' => __('The property listings banner may not be greater than 4 MB.'),
        ];
    }
}
