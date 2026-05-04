<?php

namespace Modules\Corporate\Data;

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

class TeamData extends Data
{
    public function __construct(
        #[Required, StringType, Rule('min:2', 'max:255')]
        public string $name,

        #[Nullable, StringType, Rule('max:255')]
        public ?string $position = null,

        #[Nullable, StringType, Rule('max:2048', 'url')]
        public ?string $link = null,

        #[Nullable]
        public UploadedFile|string|null $avatar = null,

        #[Nullable, IntegerType, Rule('min:0', 'max:999999')]
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
            'avatar' => ['nullable', $imageOrPath],
        ];
    }
}
