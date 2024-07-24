<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveColumnsFromTableName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('csrw_wards_stocks_logs', function (Blueprint $table) {
            $table->dropColumn('is_converted');
            $table->dropColumn('converted_from_ward_stock_id');
        });
        Schema::table('csrw_wards_stocks', function (Blueprint $table) {
            $table->dropColumn('is_converted');
            $table->dropColumn('converted_from_ward_stock_id');
            $table->dropColumn('converted_quantity');
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

        Schema::table('csrw_wards_stocks_logs', function (Blueprint $table) {
            //
        });
    }
}
