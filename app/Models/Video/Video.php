<?php

namespace App\Models\Video;

use App\User;
use App\School;
use App\Subchannel;
use App\Models\Video\Channel;
use Illuminate\Database\Eloquent\Model;
// use App\{School, User, Stream};
// use App\Models\{Subject, Grade};

class Video extends Model
{
    protected $guarded = ['id'];
    protected $casts = [
        'created_at' => 'datetime',
    ];
    public function user() {
        return $this->belongsTo(User::class);
    }
    /**
     * Get the channel that owns the Video
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }
    /**
     * Get the subchannel that owns the Video
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function subchannel()
    {
        return $this->belongsTo(Subchannel::class);
    }

    public function school()
    {
        return $this->hasOne(School::class);
    }
}
