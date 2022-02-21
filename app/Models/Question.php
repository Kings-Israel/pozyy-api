<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $guarded = [];

    public function grade(){
        return $this->belongsTo(Grade::class);
    }

    public function subject(){
        return $this->belongsTo(Subject::class);
    }

    public function topic(){
        return $this->belongsTo(Topic::class);
    }

    public function subtopic(){
        return $this->belongsTo(Subtopic::class);
    }

    public function answers(){
        return $this->hasMany(Answer::class);
    }

    // public function correct_answer(){
    //     // return "dd";
    //     // return $this->answers();

    //     return $this->answers()->where('is_answer', '==', 1)->get();
    // }

    public function solution(){
        return $this->hasOne(Solution::class);
    }

    public function image(){
        return $this->morphOne('App\Models\Image', 'imageable');
    }

    public function tests(){
        return $this->belongsToMany(Test::class, 'test_questions', 'question_id', 'test_id');
    }

    public function user() {
        return $this->belongsTo('App\User', 'created_by');
    }

}
