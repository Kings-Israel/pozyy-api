<?php

namespace App;

use App\Models\Grade;
use Illuminate\Database\Eloquent\Model;

class KidPerformance extends Model
{
    protected $guarded = [];

    /**
     * Get the kid that owns the KidPerformance
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function kid()
    {
        return $this->belongsTo(Kid::class);
    }

    /**
     * Get the grade that owns the KidPerformance
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }
}
