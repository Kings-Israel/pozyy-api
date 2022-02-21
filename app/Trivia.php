<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Trivia extends Model
{
    protected $guarded = [];

    /**
     * Get the triviaCategory that owns the Trivia
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function triviaCategory()
    {
        return $this->belongsTo(TriviaCategory::class);
    }

    /**
     * Get all of the triviaQuestions for the Trivia
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function triviaQuestions()
    {
        return $this->hasMany(TriviaQuestion::class);
    }
}
