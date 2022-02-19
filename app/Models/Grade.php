<?php

namespace App\Models;
use App\Stream;
use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    protected $guarded = [];

    public function subjects(){
        // return $this->belongsToMany(Subject::class, 'grade_subjects', 'grade_id', 'subject_id');
        return $this->hasMany(Subject::class);
    }

    public function tests(){
        return $this->hasMany(Test::class);
    }

    public function questions(){
        return $this->hasMany(Question::class);
    }

    public function topics(){
        return $this->hasMany(Topic::class);
    }
    public function streams() {
        return $this->hasMany(Stream::class);
    }
}
