<?php

namespace App;

use App\Models\Grade;
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
}
