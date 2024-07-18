<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToWardStocks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('csrw_wards_stocks', function (Blueprint $table) {
            $table->string('ris_no');
        });

        Schema::table('csrw_wards_stocks_logs', function (Blueprint $table) {
            $table->string('ris_no');
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
            $table->dropColumn('ris_no');
        });

        Schema::table('csrw_wards_stocks_logs', function (Blueprint $table) {
            $table->dropColumn('ris_no');
        });
    }
}
