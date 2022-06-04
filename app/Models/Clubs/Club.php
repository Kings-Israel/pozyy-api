<?php

namespace App\Models\Clubs;

use App\Kid;
use App\KidClub;
use App\User;

use Illuminate\Database\Eloquent\Model;

class Club extends Model
{
    protected $guarded = ['id'];
    protected $casts = [
        'created_at' => 'datetime:d-m-Y'
    ];
    public function user() {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Get all of the activities for the Club
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function activities()
    {
        return $this->hasMany(ClubActivity::class);
    }

    /**
     * The kids that belong to the Club
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function kids()
    {
        return $this->belongsToMany(Kid::class, 'kid_clubs', '', 'kid_id')->withTimestamps();
    }
}
