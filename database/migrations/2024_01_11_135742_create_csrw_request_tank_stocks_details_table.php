<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCsrwRequestTankStocksDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('csrw_request_tank_stocks_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('request_stocks_id');
            $table->string('itemcode');
            $table->integer('requested_qty');
            $table->integer('approved_qty')->nullable();
            $table->string('remarks')->nullable();
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
        Schema::dropIfExists('csrw_request_tank_stocks_details');
    }
}
