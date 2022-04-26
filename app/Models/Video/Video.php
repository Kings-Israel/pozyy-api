<?php

namespace App\Models\Video;

use Illuminate\Database\Eloquent\Model;
use App\{School, User,Stream};
use App\Models\{Subject, Grade};

class Video extends Model
{
    protected $guarded = ['id'];
    protected $casts = [
        'created_at' => 'datetime:d-m-Y',
        'subchannels' => 'array'
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
    // public function grade() {
    //     return $this->belongsTo(Grade::class);
    // }
    // public function subject() {
    //     return $this->belongsTo(Subject::class);
    // }
    public function stream() {
        return $this->belongsTo(Stream::class);
    }

    public function school()
    {
        return $this->hasOne(School::class);
    }
}
