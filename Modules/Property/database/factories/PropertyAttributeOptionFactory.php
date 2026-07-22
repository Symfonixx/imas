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
            'label' => ['en' => fake()->words(2, true)],
            'position' => fake()->numberBetween(0, 20),
            'is_active' => true,
        ];
    }
}
