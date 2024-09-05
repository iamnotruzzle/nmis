<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToStockBal extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('csrw_location_stock_balance', function (Blueprint $table) {
            $table->dateTime('end_bal_created_at')->nullable();
            $table->dateTime('beg_bal_created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('csrw_location_stock_balance', function (Blueprint $table) {
            $table->dropColumn('end_bal_created_at');
            $table->dropColumn('beg_bal_created_at');
        });
    }
}
