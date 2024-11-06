<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCsrwStockBalDateLogsColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('csrw_stock_bal_date_logs', function (Blueprint $table) {
            $table->string('beg_bal_created_at')->nullable()->change(); // Adjust type if needed
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('your_table_name', function (Blueprint $table) {
            $table->string('beg_bal_created_at')->nullable(false)->change(); // Or change to the original type if necessary
        });
    }
}
