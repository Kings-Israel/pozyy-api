<?php

namespace App\Enum;

use Spatie\Enum\Enum;

/**
 * @method static self SCHOOL_TV()
 * @method static self CLUBS()
 * @method static self INDOOR()
 * @method static self OUTDOOR()
 */

class MobileSections extends Enum {
    protected static function labels(): array
    {
        return [
            'SCHOOL_TV' => 'School TV',
            'CLUBS' => 'Clubs',
            'INDOOR' => 'Indoor',
            'OUTDOOR' => 'Outdoor'
        ];
    }
}
