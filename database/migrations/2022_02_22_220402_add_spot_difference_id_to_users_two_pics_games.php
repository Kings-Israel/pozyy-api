<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSpotDifferenceIdToUsersTwoPicsGames extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users_two_pics_games', function (Blueprint $table) {
            $table->unique(['user_id', 'spot_differences_id']);
            $table->foreignId('spot_differences_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users_two_pics_games', function (Blueprint $table) {
            $table->dropColumn('spot_differences_id');
        });
    }
}
