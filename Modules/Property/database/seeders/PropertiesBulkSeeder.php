<?php

namespace Modules\Property\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Property\Models\Location;
use Modules\Property\Models\Property;
use Modules\Property\Models\PropertyType;

class PropertiesBulkSeeder extends Seeder
{
    private const PROPERTIES_PER_TYPE = 20;

    private const PROJECT_CODE_PREFIX = 'TRK-FAK-';

    /**
     * Create 20 published factory properties for each property type.
     */
    public function run(): void
    {
        if (! Location::query()->exists()) {
            $this->command?->warn('PropertiesBulkSeeder skipped: no locations. Seed locations first.');

            return;
        }

        $this->call(PropertyTypeSeeder::class);

        $propertyTypes = PropertyType::query()->orderBy('id')->get();

        if ($propertyTypes->isEmpty()) {
            $this->command?->warn('PropertiesBulkSeeder skipped: no property types.');

            return;
        }

        foreach ($propertyTypes as $propertyType) {
            $this->seedPropertiesForType($propertyType);
        }
    }

    private function seedPropertiesForType(PropertyType $propertyType): void
    {
        $codePattern = self::PROJECT_CODE_PREFIX.$propertyType->slug.'-%';

        $existingCount = Property::query()
            ->where('property_type_id', $propertyType->id)
            ->where('project_code', 'like', $codePattern)
            ->count();

        $needed = self::PROPERTIES_PER_TYPE - $existingCount;

        if ($needed <= 0) {
            return;
        }

        $startIndex = $existingCount + 1;

        Property::factory()
            ->count($needed)
            ->published()
            ->forPropertyType($propertyType)
            ->sequence(
                ...collect(range($startIndex, $startIndex + $needed - 1))
                    ->map(
                        fn (int $index): array => [
                            'project_code' => sprintf(
                                '%s%s-%04d',
                                self::PROJECT_CODE_PREFIX,
                                $propertyType->slug,
                                $index,
                            ),
                            'url_key' => sprintf(
                                '%s%s-%04d',
                                Str::slug(self::PROJECT_CODE_PREFIX),
                                $propertyType->slug,
                                $index,
                            ),
                        ],
                    )
                    ->all(),
            )
            ->create();

        $this->command?->info(sprintf(
            'Seeded %d properties for type "%s".',
            $needed,
            $propertyType->slug,
        ));
    }
}
