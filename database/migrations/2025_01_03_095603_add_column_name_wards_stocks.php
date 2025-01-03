<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnNameWardsStocks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('csrw_wards_stocks', function (Blueprint $table) {
            $table->string('consignment')->nullable();
        });
        Schema::table('csrw_wards_stocks_logs', function (Blueprint $table) {
            $table->string('consignment')->nullable();
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
            $table->dropColumn('consignment');
        });
        Schema::table('csrw_wards_stocks_logs', function (Blueprint $table) {
            $table->dropColumn('consignment');
        });
    }
}
