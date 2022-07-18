<?php

namespace App\Enum;

use Spatie\Enum\Enum;

/**
 * @method static self GAMES()
 * @method static self EVENTS()
 */

class MobileSections extends Enum {
    protected static function labels(): array
    {
        return [
            'GAMES' => 'GAMES',
            'EVENTS' => 'Events',
        ];
    }
}
