<?php

declare(strict_types=1);

namespace Modules\Base\Support\Media;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
use Modules\Core\Support\AdminImageInput;

/**
 * Validates library-only content images: string media paths, REMOVED, or empty/null.
 * Rejects direct file uploads. Paths under media-library/ must exist in the Media Library;
 * other normalized paths are allowed so edit forms can keep legacy stored images.
 */
final class LibraryImageRule implements ValidationRule
{
    public function __construct(
        private readonly bool $allowArchived = false,
        private readonly bool $imagesOnly = true,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '' || $value === AdminImageInput::REMOVED) {
            return;
        }

        if ($value instanceof UploadedFile) {
            $fail(__('Images must be selected from the Media Library.'));

            return;
        }

        if (! is_string($value)) {
            $fail(__('The selected media is invalid.'));

            return;
        }

        $path = MediaAssetResolver::normalizePath($value);
        if ($path === null) {
            $fail(__('The selected media is invalid.'));

            return;
        }

        if (str_starts_with($path, 'media-library/')) {
            (new MediaPathRule($this->allowArchived, $this->imagesOnly))->validate($attribute, $path, $fail);
        }
    }
}
