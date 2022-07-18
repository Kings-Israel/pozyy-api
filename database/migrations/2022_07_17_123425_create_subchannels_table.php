<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubchannelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subchannels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_id')->references('id')->on('channels')->onDelete('cascade');
            $table->string('name');
            $table->string('thumbnail_url');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subchannels');
    }
}
