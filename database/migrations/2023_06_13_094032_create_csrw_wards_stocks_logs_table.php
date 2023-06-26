<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCsrwWardsStocksLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('csrw_wards_stocks_logs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('request_stocks_id');
            $table->bigInteger('request_stocks_detail_id');
            $table->bigInteger('stock_id');
            $table->string('location');
            $table->string('cl2comb');
            $table->bigInteger('brand')->nullable();
            $table->string('chrgcode')->nullable();
            $table->integer('prev_qty');
            $table->integer('new_qty');
            $table->dateTime('manufactured_date')->nullable();
            $table->dateTime('delivered_date')->nullable();
            $table->dateTime('expiration_date');
            $table->string('action');
            $table->string('remarks', 500)->nullable();
            $table->string('entry_by');
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
        Schema::dropIfExists('csrw_wards_stocks_logs');
    }
}