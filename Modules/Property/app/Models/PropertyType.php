<?php

namespace Modules\Property\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class PropertyType extends Model
{
    use HasTranslations;

    protected $table = 'property_types';

    public array $translatable = ['name'];

    protected $fillable = [
        'name',
        'slug',
        'icon',
    ];
}
