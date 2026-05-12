<?php

namespace Modules\Property\Data;

use Spatie\LaravelData\Data;

class PropertyTypeData extends Data
{
    public function __construct(
        public string $name,
        public string $slug,
        public string $icon,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toPayloadArray(): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'icon' => $this->icon,
        ];
    }
}
