<?php

namespace Modules\Cms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Cms\Database\Factories\FaqFactory;
use Spatie\Translatable\HasTranslations;

class Faq extends Model
{
    use HasFactory;
    use HasTranslations;

    protected static function newFactory(): FaqFactory
    {
        return FaqFactory::new();
    }

    public $translatable = ['question', 'answer'];

    protected $fillable = [
        'question',
        'answer',
        'rank',
        'status',
    ];

    protected $casts = [
        'rank' => 'integer',
    ];

    public function scopePublished($query)
    {
        return $query->where('status', 'Published');
    }
}
