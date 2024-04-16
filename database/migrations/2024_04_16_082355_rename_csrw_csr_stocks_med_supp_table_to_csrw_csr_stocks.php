<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameCsrwCsrStocksMedSuppTableToCsrwCsrStocks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('csrw_csr_stocks_med_supp', 'csrw_csr_stocks');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::rename('csrw_csr_stocks', 'csrw_csr_stocks_med_supp');
    }
}
