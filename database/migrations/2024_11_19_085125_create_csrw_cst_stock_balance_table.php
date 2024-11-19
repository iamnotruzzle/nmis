<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCsrwCstStockBalanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('csrw_csr_stock_balance', function (Blueprint $table) {
            $table->id();
            $table->string('cl2comb');
            $table->integer('beginning_balance');
            $table->integer('ending_balance');
            $table->dateTime('beg_bal_created_at');
            $table->dateTime('end_bal_created_at');
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
        Schema::dropIfExists('csrw_cst_stock_balance');
    }
}
