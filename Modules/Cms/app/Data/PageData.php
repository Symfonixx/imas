<?php

namespace Modules\Cms\Data;

use Modules\Base\Support\Media\LibraryImageRule;
use Modules\User\Enums\CmsStatus;
use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;

class PageData extends Data
{
    public function __construct(
        #[Required, StringType, Rule('min:2', 'max:255')]
        public string $title,

        #[Required, StringType, Rule('min:2', 'max:255')]
        public string $slug,

        #[Required, StringType]
        public string $content,

        #[Nullable, StringType, Rule('max:70')]
        public ?string $meta_title = null,

        #[Nullable, StringType, Rule('max:255')]
        public ?string $meta_description = null,

        #[Nullable, StringType, Rule('max:255')]
        public ?string $meta_keywords = null,

        #[Nullable]
        public ?string $image = null,

        #[Nullable]
        public ?string $meta_image = null,

        #[Nullable]
        public CmsStatus $status = CmsStatus::PUBLISHED,

        #[Nullable, BooleanType]
        public ?bool $featured = false,

        #[Nullable, BooleanType]
        public ?bool $add_to_nav = false,

        #[Nullable, BooleanType]
        public ?bool $add_to_footer = false,

        #[Nullable, BooleanType]
        public ?bool $add_to_top_bar = false,

        #[Nullable, BooleanType]
        public ?bool $add_to_bottom_bar = false,
    ) {}

    /**
     * @return array<string, list<mixed>>
     */
    public static function rules(?ValidationContext $context = null): array
    {
        return [
            'image' => ['nullable', new LibraryImageRule],
            'meta_image' => ['nullable', new LibraryImageRule],
        ];
    }
}
