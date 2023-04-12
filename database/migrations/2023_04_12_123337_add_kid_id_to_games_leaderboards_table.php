<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddKidIdToGamesLeaderboardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('games_leaderboards', function (Blueprint $table) {
            $table->foreignId('kid_id')->nullable()->references('id')->on('kids')->onDelete('cascade')->cascadeOnUpdate();
            $table->foreignId('gameable_id')->nullable();
            $table->string('gameable_type')->nullable();
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
            $table->dropColumn('kid_id');
            $table->dropColumn('gameable_id');
            $table->dropColumn('gameable_type');
        });
    }
}
