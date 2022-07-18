<?php

namespace App;

use App\Models\Video\Video;
use App\Models\Video\Channel;
use Illuminate\Database\Eloquent\Model;

class Subchannel extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Get the channel that owns the Subchannel
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    /**
     * Get all of the videos for the Subchannel
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function videos()
    {
        return $this->hasMany(Video::class);
    }
}
