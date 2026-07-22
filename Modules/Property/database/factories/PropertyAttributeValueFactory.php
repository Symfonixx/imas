<?php

namespace Modules\Property\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Property\Models\Property;
use Modules\Property\Models\PropertyAttribute;
use Modules\Property\Models\PropertyAttributeValue;

/**
 * @extends Factory<PropertyAttributeValue>
 */
class PropertyAttributeValueFactory extends Factory
{
    protected $model = PropertyAttributeValue::class;

    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'attribute_id' => PropertyAttribute::factory(),
            'text_value' => null,
            'decimal_value' => null,
            'boolean_value' => null,
            'integer_value' => null,
            'date_value' => null,
            'datetime_value' => null,
            'json_value' => null,
            'unique_hash' => null,
        ];
    }
}
