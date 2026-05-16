<?php

namespace Modules\User\Models;

use App\Models\User as AppUser;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Property\Models\Property;

class User extends AppUser
{
    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    /**
     * Properties this user has favorited (pivot: user_favorite_properties), newest first.
     */
    public function favoriteProperties(): BelongsToMany
    {
        return $this->belongsToMany(Property::class, 'user_favorite_properties')
            ->withPivot(['created_at'])
            ->orderByDesc('user_favorite_properties.created_at');
    }
}
