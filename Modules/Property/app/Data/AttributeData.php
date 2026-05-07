<?php

namespace Modules\Property\Data;

use Modules\Property\Enums\AttributeType;
use Spatie\LaravelData\Data;

class AttributeData extends Data
{
    public function __construct(
        public string $name,
        public string $code,
        public AttributeType $type,
        public bool $is_filterable = false,
        public bool $is_required = false,
        public bool $is_trans = false,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toPayloadArray(): array
    {
        return [
            'name' => $this->name,
            'code' => $this->code,
            'type' => $this->type->value,
            'is_filterable' => $this->is_filterable,
            'is_required' => $this->is_required,
            'is_trans' => $this->is_trans,
        ];
    }
}
