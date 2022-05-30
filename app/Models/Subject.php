<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $guarded = [];

    public function grade(){
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

    /**
     * Get the school that owns the Subject
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

}
