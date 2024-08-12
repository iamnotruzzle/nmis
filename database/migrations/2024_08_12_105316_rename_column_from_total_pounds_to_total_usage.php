<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameColumnFromTotalPoundsToTotalUsage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('csrw_wards_stocks', function (Blueprint $table) {
            $table->renameColumn('total_pounds', 'total_usage');
        });

        Schema::table('csrw_wards_stocks_logs', function (Blueprint $table) {
            $table->renameColumn('total_pounds', 'total_usage');
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
            $table->renameColumn('total_pounds', 'total_usage');
        });

        Schema::table('csrw_wards_stocks_logs', function (Blueprint $table) {
            $table->renameColumn('total_pounds', 'total_usage');
        });
    }
}
