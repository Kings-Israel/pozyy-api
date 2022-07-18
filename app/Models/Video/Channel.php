<?php

namespace App\Models\Video;

use App\Subchannel;
use App\Models\Video\Video;
use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    protected $guarded = ['id'];
    protected $casts = [
        'created_at' => 'datetime'
    ];

    /**
     * Get all of the subchannels for the Channel
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function subchannels()
    {
        return $this->hasMany(Subchannel::class);
    }

    public function videos() {
        return $this->hasMany(Video::class);
    }
}
