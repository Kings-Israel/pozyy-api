<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFilesToSolutionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('solutions', function (Blueprint $table) {
            $table->string('image')->nullable();
            $table->string('image_path')->nullable();
            $table->string('audio')->nullable();
            $table->string('audio_path')->nullable();
            $table->string('video')->nullable();
            $table->string('video_path')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('solutions', function (Blueprint $table) {
            $table->dropColumn('image');
            $table->dropColumn('image_path');
            $table->dropColumn('audio');
            $table->dropColumn('audio_path');
            $table->dropColumn('video');
            $table->dropColumn('video_path');
        });
    }
}
