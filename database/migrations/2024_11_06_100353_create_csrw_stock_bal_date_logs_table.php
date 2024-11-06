<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCsrwStockBalDateLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('csrw_stock_bal_date_logs', function (Blueprint $table) {
            $table->id();
            $table->string('wardcode');
            $table->dateTime('beg_bal_created_at');
            $table->dateTime('end_bal_created_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('csrw_stock_bal_date_logs');
    }
}
