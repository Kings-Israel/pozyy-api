<?php

namespace App\Helpers;

class NumberGenerator {
    public static function generateNumber($model, $column) {
        $number = mt_rand(1000000000, 9999999999);

        // call the same function if the number exists already
        if (self::numberExists($model, $column, $number)) {
            return generateNumber($model, $column);
        }

        // otherwise, it's valid and can be used
        return $number;
    }

    public static function numberExists($model, $column, $number) {
        // query the database and return a boolean
        return $model::where($column, $number)->exists();
    }
}
