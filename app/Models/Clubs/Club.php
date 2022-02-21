<?php

namespace App\Models\Clubs;
use App\User;

use Illuminate\Database\Eloquent\Model;

class Club extends Model
{
    protected $guarded = ['id'];
    protected $casts = [
        'created_at' => 'datetime:d-m-Y'
    ];
    public function user() {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
