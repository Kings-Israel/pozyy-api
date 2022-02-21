<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TriviaCategory extends Model
{
    protected $guarded = [];

    /**
     * Get all of the comments for the TriviaCategory
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function trivias()
    {
        return $this->hasMany(Trivia::class);
    }
}
