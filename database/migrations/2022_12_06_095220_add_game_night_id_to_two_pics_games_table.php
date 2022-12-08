<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGameNightIdToTwoPicsGamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('two_pics_games', function (Blueprint $table) {
            $table->foreignId('game_night_id')->nullable()->references('id')->on('game_nights')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('two_pics_games', function (Blueprint $table) {
            $table->dropColumn('game_night_id');
        });
    }
}
