<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTwoPicsGamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_two_pics_games', function (Blueprint $table) {
            $table->id();
            $table->unique(['user_id', 'two_pics_game_id']);
            $table->foreignId('user_id');
            $table->foreignId('two_pics_game_id');
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
        Schema::dropIfExists('users_two_pics_games');
    }
}
