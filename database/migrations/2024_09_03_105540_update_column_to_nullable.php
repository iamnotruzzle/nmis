<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateColumnToNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('csrw_location_stock_balance', function (Blueprint $table) {
            $table->integer('ending_balance')->nullable()->change();
            $table->integer('beginning_balance')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('csrw_location_stock_balance', function (Blueprint $table) {
            $table->integer('ending_balance')->nullable(false)->change();
            $table->integer('beginning_balance')->nullable(false)->change();
        });
    }
}
