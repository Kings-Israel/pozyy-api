<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
  protected $guarded = [];

  /**
   * The attributes that should be cast to native types.
   *
   * @var array
   */
  protected $casts = [
    'isPaid' => 'bool',
  ];

  /**
   * Get all of the eventUserTicket for the Event
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function eventUserTickets()
  {
    return $this->hasMany(EventUserTicket::class);
  }

  public function mpesaPayments()
  {
    return $this->morphMany(MpesaPayment::class, 'mpesa_payable');
  }

  public function userHasTicket()
  {
    $exists = EventUserTicket::where('event_id', $this->id)->where('user_id', auth()->id())->where('isPaid', true)->first();

    if ($exists) {
        return true;
    }

    return false;
  }
}
