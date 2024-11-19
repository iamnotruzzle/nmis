<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeColumnNullableInCsrwCsrStockBalanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('csrw_csr_stock_balance', function (Blueprint $table) {
            $table->integer('beginning_balance')->nullable()->change();
            $table->integer('ending_balance')->nullable()->change();
            $table->dateTime('beg_bal_created_at')->nullable()->change();
            $table->dateTime('end_bal_created_at')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('csrw_csr_stock_balance', function (Blueprint $table) {
            //
        });
    }
}
