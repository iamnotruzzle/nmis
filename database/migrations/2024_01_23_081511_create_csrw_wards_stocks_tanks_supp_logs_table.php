<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCsrwWardsStocksTanksSuppLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('csrw_wards_stocks_tanks_supp_logs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('request_stocks_id')->nullable();
            $table->bigInteger('request_stocks_detail_id')->nullable();
            $table->bigInteger('stock_id');
            $table->string('itemcode');
            $table->string('location');
            $table->integer('prev_qty');
            $table->integer('new_qty');
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
        Schema::dropIfExists('csrw_wards_stocks_tanks_supp_logs');
    }
}
