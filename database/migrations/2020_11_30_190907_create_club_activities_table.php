<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClubActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('club_activities', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('school_id');
            $table->bigInteger('club_id');
            $table->string('activity_name');
            $table->text('description');
            $table->string('image')->nullable();
            $table->string('video')->nullable();
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
        Schema::dropIfExists('club_activities');
    }
}
