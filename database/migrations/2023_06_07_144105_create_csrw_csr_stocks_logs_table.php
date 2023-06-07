<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCsrwCsrStocksLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('csrw_csr_stocks_logs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('batch_no');
            $table->string('cl2comb');
            $table->bigInteger('brand');
            $table->integer('prev_qty');
            $table->integer('new_qty');
            $table->dateTime('manufactured_date')->nullable();
            $table->dateTime('delivered_date')->nullable();
            $table->dateTime('expiration_date');
            $table->string('action');
            $table->string('remarks', 500)->nullable();
            $table->string('entry_by'); // employeeid
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
        Schema::dropIfExists('csrw_csr_stocks_logs');
    }
}
