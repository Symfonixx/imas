<?php

declare(strict_types=1);

namespace Modules\Base\Support\Media;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Modules\Base\Models\Media;

final class MediaPathRule implements ValidationRule
{
    public function __construct(
        private readonly bool $allowArchived = false,
        private readonly bool $imagesOnly = true,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || trim($value) === '') {
            $fail(__('The selected media is invalid.'));

            return;
        }

        $path = MediaAssetResolver::normalizePath($value);
        if ($path === null) {
            $fail(__('The selected media is invalid.'));

            return;
        }

        $query = Media::query()->where('path', $path)->where('disk', 'public');
        if (! $this->allowArchived) {
            $query->whereNull('archived_at');
        }

        /** @var Media|null $media */
        $media = $query->first();
        if ($media === null) {
            $fail(__('The selected media must exist in the Media Library.'));

            return;
        }

        if ($this->imagesOnly && ! $media->isImage()) {
            $fail(__('The selected media must be an image.'));
        }
    }
}
