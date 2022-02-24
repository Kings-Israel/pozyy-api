<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersGamesPlayedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_games_played', function (Blueprint $table) {
            $table->id();
            $table->unique(['user_id', 'trivia_id']);
            $table->unique(['user_id', 'two_pics_games_id']);
            $table->unique(['user_id', 'spot_difference_id']);
            $table->foreignId('user_id');
            $table->foreignId('trivia_id')->nullable();
            $table->foreignId('two_pics_games_id')->nullable();
            $table->foreignId('spot_difference_id')->nullable();
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
        Schema::dropIfExists('users_games_played');
    }
}
