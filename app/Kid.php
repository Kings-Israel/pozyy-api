<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Kid extends Model
{
    protected $guarded = ['id'];
    public function parent() {
        return $this->hasOne(User::class, 'id', 'parent_id');
    }
}
