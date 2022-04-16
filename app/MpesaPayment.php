<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MpesaPayment extends Model
{
    protected $guarded = [];

    public function mpesaPayable()
    {
        return $this->morphTo();
    }
}


