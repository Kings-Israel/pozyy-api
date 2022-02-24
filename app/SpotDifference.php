<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SpotDifference extends Model
{
    protected $guarded = [];

    protected $casts = [
        'differences' => 'array'
    ];

    public function userHasPlayed(?User $user)
    {
        if (!$user) {
            return false;
        }

        $exists = DB::table('users_games_played')->where('user_id', $user->id)->where('spot_difference_id', $this->id)->first();
        return $exists;
    }
}
