<?php

namespace Modules\Property\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Property\Models\PropertyType;

/**
 * @extends Factory<PropertyType>
 */
class PropertyTypeFactory extends Factory
{
    protected $model = PropertyType::class;

    public function definition(): array
    {
        $nameEn = fake()->unique()->words(2, true);

        return [
            'name' => [
                'en' => ucfirst($nameEn),
                'ar' => fake('ar_SA')->words(2, true),
                'tr' => ucfirst(fake()->words(2, true)),
            ],
            'slug' => Str::slug($nameEn).'-'.fake()->unique()->numberBetween(100, 999),
            'icon' => 'fa-home',
        ];
    }
}
