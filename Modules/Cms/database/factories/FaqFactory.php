<?php

namespace Modules\Cms\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Cms\Models\Faq;

/**
 * @extends Factory<Faq>
 */
class FaqFactory extends Factory
{
    protected $model = Faq::class;

    public function definition(): array
    {
        return [
            'question' => [
                'en' => fake()->unique()->sentence(rand(4, 8)).'?',
                'ar' => fake('ar_SA')->sentence(rand(4, 8)).'؟',
            ],
            'answer' => [
                'en' => fake()->paragraphs(3, true),
                'ar' => fake('ar_SA')->paragraphs(3, true),
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
