<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKidClubsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kid_clubs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kid_id')->constrained()->onDelete('cascade');
            $table->foreignId('club_id')->constrained()->onDelete('cascade');
            $table->unique(['kid_id', 'club_id']);
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
        Schema::dropIfExists('kid_clubs');
    }
}
