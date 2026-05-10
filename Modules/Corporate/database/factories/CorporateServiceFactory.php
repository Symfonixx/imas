<?php

namespace Modules\Corporate\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Corporate\Models\CorporateService;

/**
 * @extends Factory<CorporateService>
 */
class CorporateServiceFactory extends Factory
{
    protected $model = CorporateService::class;

    public function definition(): array
    {
        $titleEn = fake()->unique()->sentence(4);

        return [
            'title' => [
                'en' => $titleEn,
                'ar' => fake('ar_SA')->sentence(4),
            ],
            'slug' => Str::slug(Str::limit($titleEn, 72)).'-'.fake()->unique()->numberBetween(100, 99999),
            'description' => [
                'en' => fake()->paragraph(),
                'ar' => fake('ar_SA')->paragraph(),
            ],
            'content' => [
                'en' => '<p>'.implode('</p><p>', fake()->paragraphs(4, false)).'</p>',
                'ar' => '<p>'.implode('</p><p>', fake('ar_SA')->paragraphs(4, false)).'</p>',
            ],
            'image' => 'corporate/services/placeholder.jpg',
            'meta_image' => null,
            'meta_title' => [
                'en' => fake()->sentence(8),
                'ar' => fake('ar_SA')->sentence(8),
            ],
            'meta_description' => [
                'en' => fake()->paragraph(),
                'ar' => fake('ar_SA')->paragraph(),
            ],
            'meta_keywords' => [
                'en' => implode(', ', fake()->words(8)),
                'ar' => implode('، ', fake('ar_SA')->words(8)),
            ],
            'status' => fake()->randomElement(['Published', 'Archived']),
            'featured' => fake()->boolean(40),
            'visits' => fake()->numberBetween(0, 5000),
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Published',
        ]);
    }
}
