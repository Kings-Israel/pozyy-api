<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TwoPicsGame extends Model
{
    protected $guarded = [];

    /**
     * Get all of the users for the TwoPicsGame
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users()
    {
        return $this->hasMany(User::class, 'users_two_pics_games');
    }

    public function userHasPlayed(?User $user)
    {
        if (!$user) {
            return false;
        }

        $exists = DB::table('users_two_pics_games')->where('user_id', $user->id)->where('two_pics_game_id', $this->id)->first();
        return $exists;
    }
}
