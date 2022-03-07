<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GamesLeaderboard extends Model
{
    protected $guarded = [];

    /**
     * Get the user that owns the GamesLeaderboard
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
