<?php

namespace Modules\Property\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Property\Models\Property;
use Modules\Property\Support\PropertyListingCardSerializer;

/**
 * @mixin Property
 */
class PropertyCardResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Property $property */
        $property = $this->resource;

        return PropertyListingCardSerializer::toArray($property);
    }
}
