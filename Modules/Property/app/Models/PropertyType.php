<?php

namespace Modules\Property\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Property\Database\Factories\PropertyTypeFactory;
use Spatie\Translatable\HasTranslations;

class PropertyType extends Model
{
    use HasFactory;
    use HasTranslations;

    protected static function newFactory(): PropertyTypeFactory
    {
        return PropertyTypeFactory::new();
    }

    protected $table = 'property_types';

    public array $translatable = ['name'];

    protected $fillable = [
        'name',
        'slug',
        'icon',
    ];
}
