<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCsrwStocksLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('csrw_stocks_logs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('csrw_stocks_id');
            $table->integer('previous_qty');
            $table->integer('new_qty');
            $table->string('action');
            $table->string('remarks', 500);
            $table->string('entry_by');
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
        Schema::dropIfExists('csrw_stocks_logs');
    }
}
