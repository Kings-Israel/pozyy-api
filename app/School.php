<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    protected $guarded = ['id'];
    protected $casts = [
        'suspend' => 'boolean'
    ];

    public function admin() {
        return $this->hasOne(User::class);
    }
    public function users() {
        return $this->hasMany(User::class);
    }

    /**
     * Get all of the kids for the School
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function kids()
    {
        return $this->hasMany(Kid::class, 'school_id');
    }
}
