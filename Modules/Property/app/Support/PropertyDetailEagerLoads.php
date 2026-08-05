<?php

namespace Modules\Property\Support;

use Modules\User\Enums\CmsStatus;

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
            'slideMedia' => fn ($query) => $query
                ->whereHas(
                    'slideCategory',
                    fn ($categoryQuery) => $categoryQuery
                        ->where('status', CmsStatus::PUBLISHED->value)
                )
                ->select([
                    'id',
                    'property_id',
                    'slide_category_id',
                    'type',
                    'path',
                    'position',
                ]),
            'slideMedia.slideCategory:id,name,slug,position,status',
            'similarProperties' => fn ($query) => $query->with(PropertyCardEagerLoads::relations()),
        ];
    }
}
