<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $guarded = [];

    public function grades(){
        // return $this->belongsToMany(Grade::class, 'grade_subjects', 'subject_id', 'grade_id');
        return $this->belongsTo(Grade::class);
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

}
