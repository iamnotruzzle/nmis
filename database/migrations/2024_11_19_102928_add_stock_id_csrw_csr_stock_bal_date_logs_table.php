<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStockIdCsrwCsrStockBalDateLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('csrw_csr_stock_bal_date_logs', function (Blueprint $table) {
            $table->dropColumn('end_bal_created_at');
        });

        Schema::table('csrw_csr_stock_bal_date_logs', function (Blueprint $table) {
            $table->string('beg_bal_created_at')->nullable(); // Adjust type if needed
            $table->dateTime('end_bal_created_at')->nullable(); // Adjust type if needed
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {}
}
