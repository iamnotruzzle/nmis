<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToCsrwCsrStocksLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('csrw_csr_stocks_logs', function (Blueprint $table) {
            Schema::table('csrw_csr_stocks_logs', function (Blueprint $table) {
                $table->string('converted')->nullable();
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('csrw_csr_stocks_logs', function (Blueprint $table) {
            //
        });
    }
}
