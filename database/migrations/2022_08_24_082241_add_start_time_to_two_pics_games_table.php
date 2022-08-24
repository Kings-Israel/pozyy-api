<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStartTimeToTwoPicsGamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('two_pics_games', function (Blueprint $table) {
            $table->dateTime('start_time')->nullable()->default(now()->addWeek());
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
            $table->dropColumn('start_time');
        });
    }
}
