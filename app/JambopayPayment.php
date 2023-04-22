<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JambopayPayment extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
    /**
     * Get the user that owns the JambopayPayment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jambopayPayable()
    {
        return $this->morphTo();
    }
}
