<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MpesaPayment extends Model
{
    protected $guarded = [];

    /**
     * Get the user that owns the MpesaPayment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mpesaPayable()
    {
        return $this->morphTo()->withoutGlobalScope(SoftDeletingScope::class);
    }
}


