<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    protected $guarded = ['id'];
    public function admin() {
        return $this->hasOne(User::class);
    }
    public function users() {
        return $this->hasMany(User::class);
    }
}
