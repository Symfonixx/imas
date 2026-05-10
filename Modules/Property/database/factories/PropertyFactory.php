<?php

namespace Modules\Property\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Property\Enums\LocationType;
use Modules\Property\Models\Location;
use Modules\Property\Models\Property;
use Modules\Property\Models\PropertyType;
use Modules\User\Enums\CmsStatus;

/**
 * @extends Factory<Property>
 */
class PropertyFactory extends Factory
{
    protected $model = Property::class;

    public function definition(): array
    {
        $minArea = fake()->randomFloat(2, 55, 220);
        $maxArea = fake()->randomFloat(2, $minArea, $minArea + 120);

        return [
            'thumbnail' => 'properties/factory/placeholder-'.fake()->numberBetween(1, 5).'.jpg',
            'project_code' => 'TRK-FAK-'.fake()->unique()->numerify('######'),
            'title' => [
                'en' => fake()->sentence(rand(3, 6)),
                'ar' => fake('ar_SA')->sentence(rand(3, 6)),
                'tr' => fake()->sentence(rand(3, 6)),
            ],
            'project_name' => [
                'en' => fake()->company().' '.fake()->city(),
                'ar' => fake('ar_SA')->company(),
                'tr' => fake()->company(),
            ],
            'overview' => [
                'en' => fake()->paragraph(),
                'ar' => fake('ar_SA')->paragraph(),
                'tr' => fake()->paragraph(),
            ],
            'location_id' => $this->randomAreaLocationId(),
            'property_type_id' => PropertyType::query()->inRandomOrder()->value('id'),
            'price' => fake()->randomFloat(2, 120000, 4_500_000),
            'min_area' => $minArea,
            'max_area' => $maxArea,
            'is_sold_out' => fake()->boolean(12),
            'is_recommended' => fake()->boolean(35),
            'is_citizenship_eligible' => fake()->boolean(25),
            'is_featured' => fake()->boolean(30),
            'why_to_buy' => [
                'en' => '<p>'.fake()->paragraph().'</p>',
                'ar' => '<p>'.fake('ar_SA')->paragraph().'</p>',
                'tr' => '<p>'.fake()->paragraph().'</p>',
            ],
            'facilities' => [
                'en' => '<ul><li>'.implode('</li><li>', fake()->words(5)).'</li></ul>',
                'ar' => '<ul><li>'.implode('</li><li>', fake('ar_SA')->words(5)).'</li></ul>',
                'tr' => '<ul><li>'.implode('</li><li>', fake()->words(5)).'</li></ul>',
            ],
            'content' => [
                'en' => '<p>'.implode('</p><p>', fake()->paragraphs(3, false)).'</p>',
                'ar' => '<p>'.implode('</p><p>', fake('ar_SA')->paragraphs(3, false)).'</p>',
                'tr' => '<p>'.implode('</p><p>', fake()->paragraphs(3, false)).'</p>',
            ],
            'youtube_video_url' => fake()->optional(0.25)->url(),
            'lat' => fake()->randomFloat(7, 36.5, 41.3),
            'lng' => fake()->randomFloat(7, 26.0, 44.8),
            'status' => fake()->randomElement([CmsStatus::PUBLISHED, CmsStatus::ARCHIVED]),
            'metadata' => ['factory_seed' => true],
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CmsStatus::PUBLISHED,
        ]);
    }

    private function randomAreaLocationId(): int
    {
        $id = Location::query()
            ->where('type', LocationType::Area)
            ->inRandomOrder()
            ->value('id');

        if ($id !== null) {
            return (int) $id;
        }

        $fallback = Location::query()->inRandomOrder()->value('id');
        if ($fallback === null) {
            throw new \RuntimeException(
                'Cannot seed properties: no rows in `locations`. Seed locations first (e.g. TurkeyLocationsSeeder).'
            );
        }

        return (int) $fallback;
    }
}
