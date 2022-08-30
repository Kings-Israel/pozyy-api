<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEndTimeToSpotDifferencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('spot_differences', function (Blueprint $table) {
            $table->dateTime('end_time')->nullable()->default(now()->addWeek());
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('spot_differences', function (Blueprint $table) {
            $table->dropColumn('end_time');
        });
    }
}
