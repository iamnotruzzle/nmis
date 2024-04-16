<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameCsrwWardsStocksMedSuppTableToCsrwWardsStocks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('csrw_wards_stocks_med_supp', 'csrw_wards_stocks');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::rename('csrw_wards_stocks', 'csrw_wards_stocks_med_supp');
    }
}
