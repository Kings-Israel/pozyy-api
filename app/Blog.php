<?php

namespace App;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    protected $guarded = [];

    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'blog_title'
            ]
        ];
    }
}
