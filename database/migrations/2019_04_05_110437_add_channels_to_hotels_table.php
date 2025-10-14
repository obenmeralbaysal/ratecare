<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddChannelsToHotelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->string("odamax_url")->nullable();
            $table->string("otelz_url")->nullable();
            $table->string("tatilsepeti_url")->nullable();
            $table->boolean("booking_is_active");
            $table->boolean("hotels_is_active");
            $table->boolean("odamax_is_active");
            $table->boolean("otelz_is_active");
            $table->boolean("tatilsepeti_is_active");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->dropColumn("odamax_url");
            $table->dropColumn("otelz_url");
            $table->dropColumn("tatilsepeti_url");
            $table->dropColumn("booking_is_active");
            $table->dropColumn("hotels_is_active");
            $table->dropColumn("odamax_is_active");
            $table->dropColumn("otelz_is_active");
            $table->dropColumn("tatilsepeti_is_active");
        });
    }
}
