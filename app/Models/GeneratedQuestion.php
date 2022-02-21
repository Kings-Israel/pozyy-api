<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneratedQuestion extends Model
{
    protected $fillable = ['id'];
    protected $casts = ['answers', 'solution', 'image' => 'array'];
}
