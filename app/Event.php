<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
  protected $guarded = [];

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
}
