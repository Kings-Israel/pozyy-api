<?php

namespace App\Enum;

use Spatie\Enum\Enum;

/**
 * @method static self PARENT()
 * @method static self KIDS()
 */

class MobileSections extends Enum {
    protected static function labels(): array
    {
        return [
            'PARENT' => 'Parent',
            'KIDS' => 'Kids'
        ];
    }
}
