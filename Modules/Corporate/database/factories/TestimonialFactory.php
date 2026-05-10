<?php

namespace Modules\Corporate\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Corporate\Models\Testimonial;

/**
 * @extends Factory<Testimonial>
 */
class TestimonialFactory extends Factory
{
    protected $model = Testimonial::class;

    public function definition(): array
    {
        return [
            'name' => [
                'en' => fake()->name(),
                'ar' => fake('ar_SA')->name(),
            ],
            'client' => fake()->unique()->company(),
            'avatar' => null,
            'position' => [
                'en' => fake()->jobTitle(),
                'ar' => fake('ar_SA')->jobTitle(),
            ],
            'link' => fake()->optional()->url(),
            'quote' => [
                'en' => fake()->paragraphs(2, true),
                'ar' => fake('ar_SA')->paragraphs(2, true),
            ],
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
