<?php

namespace Modules\Cms\Data;

use Modules\Base\Support\Media\LibraryImageRule;
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
        public ?string $image = null,

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
     * @return array<string, list<mixed>>
     */
    public static function rules(?ValidationContext $context = null): array
    {
        return [
            'image' => ['nullable', new LibraryImageRule],
            'link' => ['nullable', 'string', 'max:2048'],
        ];
    }
}
