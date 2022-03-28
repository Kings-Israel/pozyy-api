<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAgeGroupToTwoPicsGamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('two_pics_games', function (Blueprint $table) {
            $table->enum('age_group', ['General Group', 'Senior Group (9-13)', 'Junior Group (Below 8 years)']);
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
            $table->dropColumn('age_group');
        });
    }
}
