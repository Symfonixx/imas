<?php

namespace Modules\Cms\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Cms\Models\BlogCategory;

/**
 * @extends Factory<BlogCategory>
 */
class BlogCategoryFactory extends Factory
{
    protected $model = BlogCategory::class;

    public function definition(): array
    {
        $nameEn = fake()->unique()->words(3, true);

        return [
            'name' => [
                'en' => $nameEn,
                'ar' => fake('ar_SA')->words(3, true),
            ],
            'slug' => Str::slug($nameEn).'-'.fake()->unique()->numberBetween(1000, 99999),
            'add_to_navbar' => fake()->boolean(25),
            'meta_title' => [
                'en' => fake()->sentence(6),
                'ar' => fake('ar_SA')->sentence(6),
            ],
            'meta_description' => [
                'en' => fake()->paragraph(),
                'ar' => fake('ar_SA')->paragraph(),
            ],
            'meta_keywords' => [
                'en' => implode(', ', fake()->words(8)),
                'ar' => implode('، ', fake('ar_SA')->words(8)),
            ],
            'meta_image' => null,
        ];
    }
}
