<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameSellingPriceToPricePerUnitInCsrwItemPrices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('csrw_item_prices', function (Blueprint $table) {
            $table->renameColumn('selling_price', 'price_per_unit');
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
            $table->renameColumn('price_per_unit', 'selling_price');
        });
    }
}
