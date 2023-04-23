<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GamesLeaderboard extends Model
{
    protected $guarded = [];

    /**
     * Get the user that owns the GamesLeaderboard
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the kid that owns the GamesLeaderboard
     */
    public function kid(): BelongsTo
    {
        return $this->belongsTo(Kid::class, 'kid_id', 'id');
    }

    /**
     * Get the gameNight that owns the GamesLeaderboard
     */
    public function gameNight(): BelongsTo
    {
        return $this->belongsTo(GameNight::class);
    }
}
