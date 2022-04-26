<?php

namespace App\Enum;

use Spatie\Enum\Enum;

/**
 * @method static self GUIDE()
 * @method static self SHOP()
 * @method static self GAME_NIGHT()
 * @method static self SCHOOL()
 */

class MobileSections extends Enum {
    protected static function labels(): array
    {
        return [
            'GUIDE' => 'Guide',
            'SHOP' => 'Shop',
            'GAME_NIGHT' => 'Game Night',
            'SCHOOL' => 'School'
        ];
    }
}
