<?php

namespace Modules\Base\Models;

use Illuminate\Database\Eloquent\Model;

class PageView extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'path',
        'route_name',
        'referrer_host',
        'visitor_hash',
        'viewed_on',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'viewed_on' => 'date',
            'created_at' => 'datetime',
        ];
    }
}
