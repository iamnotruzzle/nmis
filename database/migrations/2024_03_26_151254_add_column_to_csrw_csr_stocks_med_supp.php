<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToCsrwCsrStocksMedSupp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('csrw_csr_stocks_med_supp', function (Blueprint $table) {
            $table->decimal('acquisition_price', $precision = 8, $scale = 2)->nullable();
            $table->integer('mark_up')->nullable();
            $table->decimal('selling_price', $precision = 8, $scale = 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('csrw_csr_stocks_med_supp', function (Blueprint $table) {
            //
        });
    }
}
