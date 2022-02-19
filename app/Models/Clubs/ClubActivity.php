<?php

namespace App\Models\Clubs;

use Illuminate\Database\Eloquent\Model;

class ClubActivity extends Model
{
    protected $guarded = ['id'];
    protected $casts = [
        'created_at' => 'datetime:d-m-Y'
    ];
}
