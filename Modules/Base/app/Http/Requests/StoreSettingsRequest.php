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
            'data.header_scripts' => ['nullable', 'string', 'max:20000'],
            'data.body_scripts' => ['nullable', 'string', 'max:20000'],
            'data.footer_scripts' => ['nullable', 'string', 'max:20000'],
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
        if (is_array($media)) {
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

        // Client base64-encodes HTML fields (prefix "b64:") so WAFs do not 403 on <script>/<iframe>.
        $data = $this->input('data', []);
        if (! is_array($data)) {
            return;
        }

        foreach (['header_scripts', 'body_scripts', 'footer_scripts', 'map'] as $key) {
            if (! isset($data[$key]) || ! is_string($data[$key])) {
                continue;
            }

            $data[$key] = $this->decodeProtectedHtmlField($data[$key]);
        }

        $this->merge(['data' => $data]);
    }

    private function decodeProtectedHtmlField(string $value): string
    {
        if (! str_starts_with($value, 'b64:')) {
            return $value;
        }

        $decoded = base64_decode(substr($value, 4), true);

        return $decoded === false ? $value : $decoded;
    }
}
