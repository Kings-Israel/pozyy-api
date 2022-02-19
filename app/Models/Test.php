<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
    protected $guarded = [];

    protected $casts = [
        'topic_id' => 'array',
        'created_at' => 'datetime:d-m-Y'
    ];

    public function user(){
        return $this->belongsTo(User::class, "created_by");
    }

    public function grade(){
        return $this->belongsTo(Grade::class);
    }

    public function subject(){
        return $this->belongsTo(Subject::class);
    }

    public function category(){
        return $this->belongsTo(TestCategory::class, "test_category_id");
    }

    public function questions(){
        return $this->belongsToMany(Question::class, 'test_questions', 'test_id', 'question_id');
    }
    public function system_questions() {
        return $this->hasMany(GeneratedQuestion::class);
    }
    public function der() {
        return $this->hasMany(GeneratedQuestion::class);
    }
    public function topic() {
        return $this->belongsTo(Topic::class);
    }
}
