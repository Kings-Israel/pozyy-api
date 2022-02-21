<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserIdToGrades extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->bigInteger('school_id')->after('id')->nullable();
            $table->bigInteger('user_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->dropColumn('school_id');
            $table->dropColumn('user_id');
        });
    }
}
