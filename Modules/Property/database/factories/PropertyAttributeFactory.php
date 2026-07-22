<?php

namespace Modules\Property\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Property\Enums\AttributeType;
use Modules\Property\Models\PropertyAttribute;

/**
 * @extends Factory<PropertyAttribute>
 */
class PropertyAttributeFactory extends Factory
{
    protected $model = PropertyAttribute::class;

    public function definition(): array
    {
        return [
            'code' => fake()->unique()->slug(2),
            'name' => ['en' => fake()->words(2, true)],
            'help_text' => ['en' => fake()->optional()->sentence()],
            'type' => fake()->randomElement(AttributeType::cases()),
            'is_required' => false,
            'is_unique' => false,
            'is_active' => true,
            'validation' => null,
            'regex' => null,
            'default_value' => null,
        ];
    }
}
