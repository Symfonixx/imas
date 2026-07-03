<?php

namespace Modules\Support\Models;

use Illuminate\Database\Eloquent\Model;

class ContactForm extends Model
{
    protected $fillable = [
        'ip_address',
        'name',
        'email',
        'mobile',
        'subject',
        'source_url',
        'source_page',
        'message',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function getDisplaySourceUrlAttribute(): ?string
    {
        if (filled($this->source_url)) {
            return $this->source_url;
        }

        if ($this->looksLikeUrl($this->subject)) {
            return $this->subject;
        }

        return null;
    }

    public function getDisplaySourcePageAttribute(): ?string
    {
        if (filled($this->source_page)) {
            return $this->source_page;
        }

        if (filled($this->subject) && ! $this->looksLikeUrl($this->subject)) {
            return $this->subject;
        }

        return null;
    }

    private function looksLikeUrl(?string $value): bool
    {
        if (! filled($value)) {
            return false;
        }

        return str_starts_with($value, 'http://') || str_starts_with($value, 'https://');
    }
}
