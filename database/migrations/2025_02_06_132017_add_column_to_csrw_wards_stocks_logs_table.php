<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToCsrwWardsStocksLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('csrw_wards_stocks_logs', function (Blueprint $table) {
            $table->bigInteger('wards_stocks_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('csrw_wards_stocks_logs', function (Blueprint $table) {
            $table->dropColumn('wards_stocks_id');
        });
    }
}
