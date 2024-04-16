<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameCsrwCsrStocksMedSuppLogsTableToCsrwCsrStocksLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('csrw_csr_stocks_med_supp_logs', 'csrw_csr_stocks_logs');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::rename('csrw_csr_stocks_logs', 'csrw_csr_stocks_med_supp_logs');
    }
}
