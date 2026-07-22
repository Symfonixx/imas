<?php

namespace Modules\Property\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Property\Models\PropertyAttributeGroup;

/**
 * @extends Factory<PropertyAttributeGroup>
 */
class PropertyAttributeGroupFactory extends Factory
{
    protected $model = PropertyAttributeGroup::class;

    public function definition(): array
    {
        return [
            'name' => ['en' => fake()->words(2, true)],
            'position' => fake()->numberBetween(0, 20),
            'is_active' => true,
        ];
    }
}
