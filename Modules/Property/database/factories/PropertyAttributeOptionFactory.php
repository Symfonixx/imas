<?php

namespace Modules\Property\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Property\Models\PropertyAttribute;
use Modules\Property\Models\PropertyAttributeOption;

/**
 * @extends Factory<PropertyAttributeOption>
 */
class PropertyAttributeOptionFactory extends Factory
{
    protected $model = PropertyAttributeOption::class;

    public function definition(): array
    {
        return [
            'attribute_id' => PropertyAttribute::factory(),
            'label' => [
                'en' => fake()->words(2, true),
                'ar' => fake('ar_SA')->words(2, true),
                'tr' => fake()->words(2, true),
            ],
            'position' => fake()->numberBetween(0, 20),
            'is_active' => true,
        ];
    }

    public function forAttribute(PropertyAttribute|int $attribute): static
    {
        $id = $attribute instanceof PropertyAttribute ? $attribute->id : $attribute;

        return $this->state(fn (array $attributes): array => [
            'attribute_id' => $id,
        ]);
    }

    /**
     * @param  array<string, string>  $translations
     */
    public function labelled(array $translations, int $position = 0): static
    {
        return $this->state(fn (array $attributes): array => [
            'label' => $translations,
            'position' => $position,
        ]);
    }
}
