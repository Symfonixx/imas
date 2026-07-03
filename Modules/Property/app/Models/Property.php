<?php

namespace Modules\Property\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Base\Services\SitemapService;
use Modules\Property\Database\Factories\PropertyFactory;
use Modules\User\Enums\CmsStatus;
use Spatie\Translatable\HasTranslations;

class Property extends Model
{
    use HasFactory;
    use HasTranslations;

    protected static function booted(): void
    {
        $forget = static fn () => app(SitemapService::class)->forgetCache();

        static::saved($forget);
        static::deleted($forget);
    }

    protected static function newFactory(): PropertyFactory
    {
        return PropertyFactory::new();
    }

    protected $table = 'properties';

    public array $translatable = [
        'title',
        'project_name',
        'overview',
        'why_to_buy',
        'facilities',
        'content',
    ];

    protected $fillable = [
        'thumbnail',
        'project_code',
        'title',
        'project_name',
        'overview',
        'location_id',
        'property_type_id',
        'price',
        'min_area',
        'max_area',
        'is_sold_out',
        'is_recommended',
        'is_citizenship_eligible',
        'why_to_buy',
        'facilities',
        'content',
        'youtube_video_url',
        'lat',
        'lng',
        'status',
        'is_featured',
        'metadata',
    ];

    protected $casts = [
        'location_id' => 'integer',
        'property_type_id' => 'integer',
        'price' => 'decimal:2',
        'min_area' => 'decimal:2',
        'max_area' => 'decimal:2',
        'is_sold_out' => 'boolean',
        'is_recommended' => 'boolean',
        'is_citizenship_eligible' => 'boolean',
        'is_featured' => 'boolean',
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
        'metadata' => 'array',
        'status' => CmsStatus::class,
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function propertyType(): BelongsTo
    {
        return $this->belongsTo(PropertyType::class, 'property_type_id');
    }

    public function slides(): HasMany
    {
        return $this->hasMany(PropertySlide::class, 'property_id')->orderBy('position');
    }

    public function unitTypes(): HasMany
    {
        return $this->hasMany(UnitType::class, 'property_id')->orderBy('id');
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(UserFavoriteProperty::class, 'property_id');
    }

    public function similarProperties(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'property_similar_properties',
            'property_id',
            'similar_property_id'
        )->withPivot('sort_order')->orderByPivot('sort_order');
    }

    /**
     * @param  Builder<Property>  $query
     * @return Builder<Property>
     */
    public function scopeWithFavoriteStateForUser(Builder $query, ?int $userId): Builder
    {
        if ($userId !== null) {
            $query->withExists([
                'favorites as is_favorited' => fn (Builder $favoriteQuery) => $favoriteQuery->where('user_id', $userId),
            ]);
        }

        return $query;
    }

    /**
     * Front-office show URLs use the unique project code (admin “URL slug”).
     */
    public function getRouteKeyName(): string
    {
        return 'project_code';
    }
}
