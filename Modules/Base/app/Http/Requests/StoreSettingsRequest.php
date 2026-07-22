<?php

namespace Modules\Base\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Base\Support\Media\LibraryImageRule;

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
        $mediaPath = ['nullable', new LibraryImageRule];

        return [
            'imgs_media' => ['nullable', 'array'],
            'imgs_media.white_logo' => $mediaPath,
            'imgs_media.black_logo' => $mediaPath,
            'imgs_media.admin_logo' => $mediaPath,
            'imgs_media.meta_img' => $mediaPath,
            'imgs_media.contact_us_banner' => $mediaPath,
            'imgs_media.blog_show_banner' => $mediaPath,
            'imgs_media.property_show_banner' => $mediaPath,
            'imgs_remove' => ['nullable', 'array'],
            'imgs_remove.*' => ['nullable'],
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

    protected function prepareForValidation(): void
    {
        $media = $this->input('imgs_media', []);
        if (! is_array($media)) {
            return;
        }

        $cleaned = [];
        foreach ($media as $key => $path) {
            if (! is_string($path)) {
                $cleaned[$key] = null;

                continue;
            }

            $path = trim($path);
            $cleaned[$key] = ($path === '' || strcasecmp($path, 'null') === 0) ? null : $path;
        }

        $this->merge(['imgs_media' => $cleaned]);
    }
}
