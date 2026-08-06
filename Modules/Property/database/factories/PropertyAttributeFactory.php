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
            'name' => [
                'en' => fake()->words(2, true),
                'ar' => fake('ar_SA')->words(2, true),
                'tr' => fake()->words(2, true),
            ],
            'help_text' => null,
            'image' => null,
            'type' => fake()->randomElement(AttributeType::cases()),
            'is_required' => false,
            'is_unique' => false,
            'is_active' => true,
            'validation' => null,
            'regex' => null,
            'default_value' => null,
        ];
    }

    public function ofType(AttributeType $type): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => $type,
        ]);
    }

    public function withCode(string $code): static
    {
        return $this->state(fn (array $attributes): array => [
            'code' => $code,
        ]);
    }

    /**
     * @param  array<string, string>  $translations
     */
    public function named(array $translations): static
    {
        return $this->state(fn (array $attributes): array => [
            'name' => $translations,
        ]);
    }

    public function withIcon(?string $path): static
    {
        return $this->state(fn (array $attributes): array => [
            'image' => $path,
        ]);
    }
}
