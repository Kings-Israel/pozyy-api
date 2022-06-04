<?php

namespace App\Models\Clubs;

use Illuminate\Database\Eloquent\Model;

class ClubActivity extends Model
{
    protected $guarded = ['id'];
    protected $casts = [
        'created_at' => 'datetime'
    ];

    /**
     * Get the club that owns the ClubActivity
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function club()
    {
        return $this->belongsTo(Club::class);
    }
}
