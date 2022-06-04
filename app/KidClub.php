<?php

namespace App;

use App\Models\Clubs\Club;
use Illuminate\Database\Eloquent\Model;

class KidClub extends Model
{
    protected $guarded = [];

    /**
     * Get the kid that owns the KidClub
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function kid()
    {
        return $this->belongsTo(Kid::class);
    }

    /**
     * Get the club that owns the KidClub
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function club()
    {
        return $this->belongsTo(Club::class);
    }
}
