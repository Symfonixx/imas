<?php

namespace Modules\Property\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Property\Models\PropertyType;

class PropertyTypeSeeder extends Seeder
{
    /**
     * Default front-office property types (idempotent by slug).
     */
    public function run(): void
    {
        $types = [
            [
                'slug' => 'apartment',
                'name' => ['en' => 'Apartment', 'ar' => 'شقة', 'tr' => 'Daire'],
                'icon' => 'fa-building',
            ],
            [
                'slug' => 'villa',
                'name' => ['en' => 'Villa', 'ar' => 'فيلا', 'tr' => 'Villa'],
                'icon' => 'fa-home',
            ],
            [
                'slug' => 'house',
                'name' => ['en' => 'House', 'ar' => 'منزل', 'tr' => 'Ev'],
                'icon' => 'fa-house-user',
            ],
            [
                'slug' => 'commercial',
                'name' => ['en' => 'Commercial', 'ar' => 'تجاري', 'tr' => 'Ticari'],
                'icon' => 'fa-store',
            ],
            [
                'slug' => 'land',
                'name' => ['en' => 'Land', 'ar' => 'أرض', 'tr' => 'Arsa'],
                'icon' => 'fa-map',
            ],
        ];

        foreach ($types as $type) {
            PropertyType::query()->updateOrCreate(
                ['slug' => $type['slug']],
                [
                    'name' => $type['name'],
                    'icon' => $type['icon'],
                ],
            );
        }
    }
}
