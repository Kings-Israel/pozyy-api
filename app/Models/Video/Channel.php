<?php

namespace App\Models\Video;

use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    protected $guarded = ['id'];
    protected $casts = [
        'created_at' => 'datetime:d-m-Y'
    ];
    
    public function videos() {
        return $this->hasMany(Video::class);
    }
}
