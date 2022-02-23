<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

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

        $exists = DB::table('users_two_pics_games')->where('user_id', $user->id)->where('spot_differences_id', $this->id)->first();
        return $exists;
    }
}
