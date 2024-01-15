<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCsrwWardsStocksTanksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('csrw_wards_stocks_tanks', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('request_stocks_id')->nullable();
            $table->bigInteger('request_stocks_detail_id')->nullable();
            $table->string('itemcode');
            // $table->string('uomcode')->nullable();
            $table->integer('quantity');
            $table->string('location');
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
        Schema::dropIfExists('csrw_wards_stocks_tanks');
    }
}
