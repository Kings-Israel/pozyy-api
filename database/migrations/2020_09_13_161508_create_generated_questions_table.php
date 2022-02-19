<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGeneratedQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('generated_questions', function (Blueprint $table) {
            $table->id();
            $table->string('test_id');
            $table->string('question_name');
            $table->string('grade_name');
            $table->string('subject_name');
            $table->string('topic_name');
            $table->string('subtopic_name');
            $table->string('image_path')->nullable();
            $table->text('answers');
            $table->text('solution');
            $table->text('image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('generated_questions');
    }
}
