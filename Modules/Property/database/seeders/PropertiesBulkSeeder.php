<?php

namespace Modules\Property\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Property\Models\Location;
use Modules\Property\Models\Property;

class PropertiesBulkSeeder extends Seeder
{
    /**
     * Create ~20 factory-generated properties (requires locations in DB).
     */
    public function run(): void
    {
        if (! Location::query()->exists()) {
            $this->command?->warn('PropertiesBulkSeeder skipped: no locations. Run location seeds/migrations first.');

            return;
        }

        if (Property::query()->where('project_code', 'like', 'TRK-FAK-%')->count() >= 20) {
            return;
        }

        Property::factory()
            ->count(20)
            ->published()
            ->create();
    }
}
