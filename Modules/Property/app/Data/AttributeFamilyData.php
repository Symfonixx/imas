<?php

namespace Modules\Property\Data;

use Spatie\LaravelData\Data;

class AttributeFamilyData extends Data
{
    public function __construct(
        public string $name,
        public string $code,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toPayloadArray(): array
    {
        return [
            'name' => $this->name,
            'code' => $this->code,
        ];
    }
}
