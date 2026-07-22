<?php

namespace Modules\Corporate\Data;

use Modules\Base\Support\Media\LibraryImageRule;
use Modules\User\Enums\CmsStatus;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;

class TestimonialData extends Data
{
    public function __construct(
        #[Required, StringType, Rule('min:2', 'max:255')]
        public string $name,

        #[Required, StringType, Rule('min:1', 'max:255')]
        public string $client,

        #[Nullable, StringType, Rule('max:255')]
        public ?string $position,

        #[Required, StringType, Rule('min:2', 'max:2000')]
        public string $quote,

        #[Nullable, StringType, Rule('max:2048')]
        public ?string $link = null,

        #[Nullable]
        public ?string $avatar = null,

        #[IntegerType, Rule('min:0', 'max:999999')]
        public int $rank = 0,

        public CmsStatus $status = CmsStatus::PUBLISHED,
    ) {}

    /**
     * @return array<string, list<mixed>>
     */
    public static function rules(?ValidationContext $context = null): array
    {
        return [
            'avatar' => ['nullable', new LibraryImageRule],
            'link' => ['nullable', 'string', 'max:2048', 'url'],
        ];
    }
}
