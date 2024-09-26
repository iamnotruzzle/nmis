<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToBalanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('csrw_location_stock_balance', function (Blueprint $table) {
            $table->bigInteger('ward_stock_id')->nullable();
            $table->bigInteger('price_id')->nullable();
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
            $table->bigInteger('ward_stock_id');
            $table->dropColumn('price_id');
        });
    }
}