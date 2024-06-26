<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('isn_no')->unique();
            $table->string('book_name');
            $table->string('author');
            $table->double('price');
            $table->double('c_price');
            $table->string('grade');
            $table->text('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('user_id');
            $table->boolean('suspend')->default(false);
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
        Schema::dropIfExists('books');
    }
}
