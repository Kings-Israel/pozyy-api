<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTwoPicsGamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('two_pics_games', function (Blueprint $table) {
            $table->id();
            $table->string('pictureOne');
            $table->string('pictureTwo');
            $table->string('answer');
            $table->text('hint')->nullable();
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
        Schema::dropIfExists('two_pics_games');
    }
}
