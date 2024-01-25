<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCsrwLocationTankStockBalanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('csrw_location_tank_stock_balance', function (Blueprint $table) {
            $table->id();
            $table->string('location');
            $table->string('itemcode');
            $table->integer('ending_balance');
            $table->integer('beginning_balance');
            $table->string('entry_by');
            $table->string('updated_by')->nullable();
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
        Schema::dropIfExists('csrw_location_tank_stock_balance');
    }
}
