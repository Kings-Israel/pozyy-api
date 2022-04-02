<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EventUserTicket extends Model
{
    /**
     * Get the user that owns the EventUserTicket
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the event that owns the EventUserTicket
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
