<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TriviaQuestion extends Model
{
    protected $guarded = [];

    protected $casts = [
        'options' => 'array',
    ];

    /**
     * Get the trivia that owns the TriviaQuestion
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function trivia()
    {
        return $this->belongsTo(Trivia::class);
    }

    public function userHasPlayed(?User $user)
    {
        if (!$user) {
            return false;
        }

        $exists = DB::table('users_games_played')->where('user_id', $user->id)->where('trivia_id', $this->id)->first();
        if ($exists) {
            return true;
        }
        return false;
    }
}
