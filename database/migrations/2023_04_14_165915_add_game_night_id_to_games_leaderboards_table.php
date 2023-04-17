<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGameNightIdToGamesLeaderboardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('games_leaderboards', function (Blueprint $table) {
            $table->foreignId('game_night_id')->nullable()->references('id')->on('game_nights')->onDelete(null)->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('games_leaderboards', function (Blueprint $table) {
            $table->dropColumn('game_night_id');
        });
    }
}
