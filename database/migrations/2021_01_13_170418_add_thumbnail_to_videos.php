<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddThumbnailToVideos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->string('thumbnail')->nullable();
        });
    }

    
}
