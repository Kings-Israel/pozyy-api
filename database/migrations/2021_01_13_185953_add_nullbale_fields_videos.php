<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNullbaleFieldsVideos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->bigInteger('user_id')->nullable()->change();
            $table->bigInteger('school_id')->nullable()->change();
            $table->bigInteger('stream_id')->nullable()->change();
        });
    }

}
