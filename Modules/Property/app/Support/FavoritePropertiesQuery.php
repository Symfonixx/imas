<?php

namespace Modules\Property\Support;

use Illuminate\Database\Eloquent\Builder;
use Modules\Property\Models\Property;
use Modules\User\Enums\CmsStatus;

final class FavoritePropertiesQuery
{
    /**
     * Published properties favorited by the given user (newest favorite first).
     *
     * @return Builder<Property>
     */
    public static function publishedForUser(int $userId): Builder
    {
        return Property::query()
            ->select('properties.*')
            ->join('user_favorite_properties', function ($join) use ($userId): void {
                $join->on('properties.id', '=', 'user_favorite_properties.property_id')
                    ->where('user_favorite_properties.user_id', '=', $userId);
            })
            ->where('properties.status', CmsStatus::PUBLISHED)
            ->with(PropertyCardEagerLoads::relations())
            ->orderByDesc('user_favorite_properties.created_at');
    }
}
