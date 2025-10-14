<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRateComparisonCurrenciesTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('rate_comparison_currencies', function (Blueprint $table) {
      $table
        ->integer('hotel_id')
        ->unsigned()
        ->nullable();
      $table
        ->foreign('hotel_id')
        ->references('id')
        ->on('hotels')
        ->onDelete('cascade');
      $table->string("currency");
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('rate_comparison_currencies');
  }
}
