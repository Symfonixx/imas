<?php

namespace Modules\Property\Support;

final class PropertyCardEagerLoads
{
    /**
     * Relations required for {@see PropertyListingCardSerializer}.
     *
     * @return list<string>
     */
    public static function relations(): array
    {
        return [
            'unitTypes:id,property_id,catalog_id,name,min_area,max_area,price',
            'location:id,name,type,parent_id',
            'location.parent:id,name,type,parent_id',
            'location.parent.parent:id,name,type,parent_id',
            'propertyType:id,name,slug',
        ];
    }
}
