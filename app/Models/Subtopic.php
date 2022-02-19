<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subtopic extends Model
{
    protected $guarded = [];

    public function topic(){
        return $this->belongsTo(Topic::class);
    }

    public function questions(){
        return $this->hasMany(Question::class);
    }
}
