<?php

namespace Modules\Cms\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Cms\Models\Blog;
use Modules\Cms\Models\BlogCategory;

/**
 * @extends Factory<Blog>
 */
class BlogFactory extends Factory
{
    protected $model = Blog::class;

    public function definition(): array
    {
        $titleEn = fake()->unique()->sentence(6);

        return [
            'category_id' => BlogCategory::factory(),
            'title' => [
                'en' => $titleEn,
                'ar' => fake('ar_SA')->sentence(6),
            ],
            'slug' => Str::slug(Str::limit($titleEn, 60)).'-'.fake()->unique()->numberBetween(100, 99999),
            'description' => [
                'en' => fake()->paragraphs(2, true),
                'ar' => fake('ar_SA')->paragraphs(2, true),
            ],
            'content' => [
                'en' => '<p>'.implode('</p><p>', fake()->paragraphs(5, false)).'</p>',
                'ar' => '<p>'.implode('</p><p>', fake('ar_SA')->paragraphs(5, false)).'</p>',
            ],
            'image' => 'cms/blogs/placeholder.jpg',
            'meta_image' => 'cms/blogs/placeholder-meta.jpg',
            'meta_title' => [
                'en' => fake()->sentence(8),
                'ar' => fake('ar_SA')->sentence(8),
            ],
            'meta_description' => [
                'en' => fake()->paragraph(),
                'ar' => fake('ar_SA')->paragraph(),
            ],
            'meta_keywords' => [
                'en' => implode(', ', fake()->words(10)),
                'ar' => implode('، ', fake('ar_SA')->words(10)),
            ],
            'status' => fake()->randomElement(['Published', 'Archived']),
            'featured' => fake()->boolean(60),
            'visits' => fake()->numberBetween(0, 8000),
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Published',
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'featured' => true,
            'status' => 'Published',
        ]);
    }
}
