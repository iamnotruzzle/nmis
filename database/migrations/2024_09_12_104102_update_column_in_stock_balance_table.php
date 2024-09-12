<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateColumnInStockBalanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('csrw_location_stock_balance', function (Blueprint $table) {
            // Rename the column
            $table->renameColumn('ward_stock_id', 'ris_no');

            // Update the datatype of the new column (example: changing from integer to string)
            // $table->string('ris_no')->change();
        });

        Schema::table('csrw_location_stock_balance', function (Blueprint $table) {
            $table->string('ris_no')->change();
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
            // Reverse the changes
            $table->renameColumn('ris_no', 'ward_stock_id');

            // Revert the datatype change (example: changing back to integer)
            // $table->bigInteger('ward_stock_id')->change();
        });

        Schema::table('csrw_location_stock_balance', function (Blueprint $table) {
            $table->bigInteger('ward_stock_id')->change();
        });
    }
}
