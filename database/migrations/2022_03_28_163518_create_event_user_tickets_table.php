<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventUserTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event_user_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('event_id');
            $table->boolean('isPaid')->default(false);
            $table->string('mpesa_checkout_request_id')->nullable();
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
        Schema::dropIfExists('event_user_tickets');
    }
}
