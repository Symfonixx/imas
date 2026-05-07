<?php

namespace Modules\Property\Data;

use Modules\Property\Enums\LocationType;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class LocationData extends Data
{
    public function __construct(
        #[Required, StringType, Rule('min:2', 'max:255')]
        public string $name,

        #[Required]
        public LocationType $type,

        #[Nullable, IntegerType, Exists('locations', 'id')]
        public ?int $parent_id = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toPayloadArray(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type->value,
            'parent_id' => $this->parent_id,
        ];
    }
}
