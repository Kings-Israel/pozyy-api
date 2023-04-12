<?php

namespace App;

use App\Models\Clubs\Club;
use App\Models\Grade;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kid extends Model
{
    protected $guarded = ['id'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id', 'id');
    }

    /**
     * Get the school associated with the Kid
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the grade that owns the Kid
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function grade()
    {
        return $this->belongsTo(Grade::class);
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

    /**
     * Get all of the kidPerfomances for the Kid
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function performances()
    {
        return $this->hasMany(KidPerformance::class, 'kid_id');
    }

    /**
     * The clubs that belong to the Kid
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function clubs()
    {
        return $this->belongsToMany(Club::class, 'kid_clubs', '', 'club_id');
    }
}
