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
    private const VALUE_COLUMNS = [
        'text_value',
        'decimal_value',
        'boolean_value',
        'integer_value',
        'date_value',
        'datetime_value',
        'json_value',
    ];

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

    /**
     * Every value column nulled except the one the attribute type stores into.
     *
     * @return array<string, mixed>
     */
    public static function columnsFor(PropertyAttribute $attribute, mixed $value): array
    {
        return array_merge(
            array_fill_keys(self::VALUE_COLUMNS, null),
            [
                $attribute->type->valueColumn() => $value,
                'unique_hash' => null,
            ],
        );
    }

    public function typed(PropertyAttribute $attribute, mixed $value): static
    {
        return $this->state(fn (array $attributes): array => array_merge(
            ['attribute_id' => $attribute->id],
            self::columnsFor($attribute, $value),
        ));
    }
}
