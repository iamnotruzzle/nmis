<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTotalUsageToCsrwWardsStocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('csrw_wards_stocks', function (Blueprint $table) {
            $table->integer('total_usage')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('csrw_wards_stocks', function (Blueprint $table) {
            $table->dropColumn('paid');
        });
    }
}
