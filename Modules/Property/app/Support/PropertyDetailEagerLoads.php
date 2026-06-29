<?php

namespace Modules\Property\Support;

final class PropertyDetailEagerLoads
{
    /**
     * Relations required for {@see PropertyDetailSerializer}.
     *
     * @return list<string>
     */
    public static function relations(): array
    {
        return [
            ...PropertyCardEagerLoads::relations(),
            'slides:id,property_id,image,position',
            'similarProperties' => fn ($query) => $query->with(PropertyCardEagerLoads::relations()),
        ];
    }
}
