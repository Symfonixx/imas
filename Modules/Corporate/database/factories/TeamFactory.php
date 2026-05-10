<?php

namespace Modules\Corporate\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Corporate\Models\Team;

/**
 * @extends Factory<Team>
 */
class TeamFactory extends Factory
{
    protected $model = Team::class;

    public function definition(): array
    {
        return [
            'name' => [
                'en' => fake()->name(),
                'ar' => fake('ar_SA')->name(),
            ],
            'avatar' => null,
            'position' => [
                'en' => fake()->jobTitle(),
                'ar' => fake('ar_SA')->jobTitle(),
            ],
            'link' => fake()->optional()->url(),
            'rank' => fake()->numberBetween(0, 100),
            'status' => fake()->randomElement(['Published', 'Archived']),
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Published',
        ]);
    }
}
