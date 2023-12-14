<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCsrwWardsStocksMedSuppTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('csrw_wards_stocks_med_supp', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('request_stocks_id')->nullable();
            $table->bigInteger('request_stocks_detail_id')->nullable();
            $table->bigInteger('stock_id')->nullable();
            $table->string('location');
            $table->string('cl2comb');
            $table->string('uomcode')->nullable();
            $table->bigInteger('brand')->nullable();
            $table->string('chrgcode')->nullable();
            $table->integer('quantity');
            $table->bigInteger('converted_from_ward_stock_id')->nullable();
            $table->string('from');
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
        Schema::dropIfExists('csrw_wards_stocks_med_supp');
    }
}
