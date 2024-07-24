<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdditionalColumnToCsrwWardsStocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('csrw_wards_stocks', function (Blueprint $table) {
            $table->string('is_consumable')->nullable();
            $table->integer('average')->nullable();
            $table->integer('total_consumed')->nullable();
        });

        Schema::table('csrw_wards_stocks_logs', function (Blueprint $table) {
            $table->string('is_consumable')->nullable();
            $table->integer('average')->nullable();
            $table->integer('total_consumed')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('csrw_wards_stocks', function (Blueprint $table) {
            //
        });
    }
}
