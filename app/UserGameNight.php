<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserGameNight extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Get the gameNight that owns the UserGameNight
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function gameNight()
    {
        return $this->belongsTo(GameNight::class);
    }

    /**
     * Get the user that owns the UserGameNight
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
