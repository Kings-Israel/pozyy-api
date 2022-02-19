<?php

namespace App;

use App\Casts\Json;
use Illuminate\Database\Eloquent\Model;

class TriviaQuestion extends Model
{
    protected $guarded = [];

    protected $casts = [
        'options' => 'array',
    ];

    /**
     * Get the trivia that owns the TriviaQuestion
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function trivia()
    {
        return $this->belongsTo(Trivia::class);
    }
}
