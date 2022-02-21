<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    protected $guarded = [];

    public function subject(){
        return $this->belongsTo(Subject::class);
    }

    public function subtopics(){
        return $this->hasMany(Subtopic::class);
    }

    public function questions(){
        return $this->hasMany(Question::class);
    }
}
