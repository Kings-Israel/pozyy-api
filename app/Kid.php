<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Kid extends Model
{
    protected $guarded = ['id'];

    public function parent()
    {
        return $this->hasOne(User::class, 'id', 'parent_id');
    }

    /**
     * Get the school associated with the Kid
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function school()
    {
        return $this->belongsTo(School::class, 'id', 'school_id');
    }

    /**
     * Get the leaderboard associated with the Kid
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function leaderboard()
    {
        return $this->hasOne(GamesLeaderboard::class, 'user_id');
    }
}
