<?php

namespace Modules\Base\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Country extends Model
{
    public $timestamps = false;

    protected $appends = ['flag'];

    protected $fillable = ['name', 'iso_code_2', 'iso_code_3', 'phone_code'];

    /**
     * SVGs under `public/images/flags/` use English slug names (e.g. american-samoa.svg),
     * not ISO codes (as.svg). Resolve via ICU region name slug plus known pack mismatches.
     */
    public function getFlagAttribute(): string
    {
        $iso = strtoupper(trim((string) ($this->attributes['iso_code_2'] ?? '')));

        if ($iso === '' || strlen($iso) !== 2) {
            return asset('images/flags/flag.svg');
        }

        return asset('images/flags/'.self::resolveFlagBasename($iso).'.svg');
    }

    private static function resolveFlagBasename(string $iso2): string
    {
        static $overrides = [
            'BA' => 'bosnia-and-herzegovina',
            'CD' => 'democratic-republic-of-congo',
            'CG' => 'republic-of-the-congo',
            'CI' => 'ivory-coast',
            'CZ' => 'czech-republic',
            'MK' => 'republic-of-macedonia',
            'MM' => 'myanmar',
            'SZ' => 'swaziland',
            'TR' => 'turkey',
        ];

        $dir = public_path('images/flags');

        if (isset($overrides[$iso2]) && is_file($dir.'/'.$overrides[$iso2].'.svg')) {
            return $overrides[$iso2];
        }

        $region = locale_get_display_region('-'.$iso2, 'en');
        if ($region !== '') {
            $slug = Str::slug($region);
            if ($slug !== '' && is_file($dir.'/'.$slug.'.svg')) {
                return $slug;
            }
        }

        $lower = strtolower($iso2);
        if (is_file($dir.'/'.$lower.'.svg')) {
            return $lower;
        }

        return 'flag';
    }
}
