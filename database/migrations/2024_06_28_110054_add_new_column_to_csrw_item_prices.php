<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewColumnToCsrwItemPrices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('csrw_item_prices', function (Blueprint $table) {
            $table->string('ris_no')->nullable();
            $table->decimal('acquisition_price', $precision = 8, $scale = 2)->nullable();
            $table->decimal('hospital_price', $precision = 8, $scale = 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('csrw_item_prices', function (Blueprint $table) {
            $table->dropColumn('ris_no');
            $table->dropColumn('acquisition_price');
            $table->dropColumn('hospital_price');
        });
    }
}
