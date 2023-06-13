<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCsrwWardsStocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('csrw_wards_stocks', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('request_stocks_id');
            $table->bigInteger('request_stocks_detail_id');
            $table->bigInteger('stock_id');
            $table->string('location');
            $table->string('cl2comb');
            $table->bigInteger('brand')->nullable();
            $table->integer('quantity');
            $table->dateTime('manufactured_date')->nullable();
            $table->dateTime('delivered_date')->nullable();
            $table->dateTime('expiration_date');
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
        Schema::dropIfExists('csrw_wards_stocks');
    }
}
