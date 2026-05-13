<?php

namespace Modules\Property\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class ProjectUnitType extends Model
{
    use HasTranslations;

    protected $table = 'project_unit_types';

    public array $translatable = ['name'];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function propertyUnitTypes(): HasMany
    {
        return $this->hasMany(UnitType::class, 'catalog_id');
    }
}
