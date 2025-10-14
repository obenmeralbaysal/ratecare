<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWidgetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('widgets', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('hotel_id')->unsigned()->nullable();
            $table->foreign('hotel_id')->references('id')->on('users')->onDelete('cascade');
            $table->integer('language_id')->unsigned()->nullable();
            $table->foreign('language_id')->references('id')->on('languages')->onDelete('cascade');
            $table->string("code")->nullable();
            $table->integer("duration")->nullable();
            $table->string("main_title")->nullable();
            $table->string("reservation_button_text")->nullable();
            $table->string("direct_reservation_text")->nullable();
            $table->string("features_text")->nullable();
            $table->string("color")->nullable();
            $table->float("discount")->nullable();
            $table->string("promotion_text")->nullable();
            $table->boolean("is_active")->default(1);
            $table->string("explanation_text")->nullable();
            $table->string("promotion_code")->nullable();
            $table->string("type");

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
        Schema::dropIfExists('widgets');
    }
}
