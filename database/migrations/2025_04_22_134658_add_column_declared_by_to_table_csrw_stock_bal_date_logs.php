<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnDeclaredByToTableCsrwStockBalDateLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('csrw_stock_bal_date_logs', function (Blueprint $table) {
            $table->string('beg_bal_declared_by')->nullable();
            $table->string('end_bal_declared_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('csrw_stock_bal_date_logs', function (Blueprint $table) {
            //
        });
    }
}
