<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestCategory extends Model
{
    protected $guarded = [];
    
    public function tests(){
        return $this->hasMany(Test::class);
        // return $this->hasMany(Test::class, "test_category_id");
    }
}
