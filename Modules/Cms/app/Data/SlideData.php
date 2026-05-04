<?php

namespace Modules\Cms\Data;

use Closure;
use Illuminate\Http\UploadedFile;
use Modules\User\Enums\CmsStatus;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;

class SlideData extends Data
{
    public function __construct(
        #[Nullable]
        public UploadedFile|string|null $image = null,

        #[Nullable, StringType, Rule('max:255')]
        public ?string $main_title = null,

        #[Nullable, StringType, Rule('max:500')]
        public ?string $subtitle = null,

        #[Nullable, StringType, Rule('max:2048')]
        public ?string $link = null,

        #[Required, IntegerType, Rule('min:0')]
        public int $rank = 0,

        #[Nullable]
        public CmsStatus $status = CmsStatus::PUBLISHED,
    ) {}

    /**
     * @return array<string, list<string|Closure>>
     */
    public static function rules(?ValidationContext $context = null): array
    {
        $imageOrPath = function (string $attribute, mixed $value, Closure $fail): void {
            if ($value === null || $value === '') {
                return;
            }
            if ($value instanceof UploadedFile) {
                if (! $value->isValid()) {
                    $fail(__('The :attribute is not a valid file.'));
                }

                return;
            }
            if (! is_string($value)) {
                $fail(__('The :attribute must be a string.'));
            }
        };

        return [
            'image' => ['nullable', $imageOrPath],
            'link' => ['nullable', 'string', 'max:2048'],
        ];
    }
}
