<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ShopItem extends Model
{
    protected $guarded = [];

    public function mpesaPayments()
    {
        return $this->morphMany(MpesaPayment::class, 'mpesa_payable');
    }
}
