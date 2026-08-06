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
            'name' => [
                'en' => fake()->words(2, true),
                'ar' => fake('ar_SA')->words(2, true),
                'tr' => fake()->words(2, true),
            ],
            'position' => fake()->numberBetween(0, 20),
            'is_active' => true,
        ];
    }

    /**
     * @param  array<string, string>  $translations
     */
    public function named(array $translations, int $position = 0): static
    {
        return $this->state(fn (array $attributes): array => [
            'name' => $translations,
            'position' => $position,
        ]);
    }
}
