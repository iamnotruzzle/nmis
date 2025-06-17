<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyHospitalPricePrecisionInYourCsrwItemPrices extends Migration
{
    public function up()
    {
        Schema::table('csrw_item_prices', function (Blueprint $table) {
            $table->decimal('hospital_price', 12, 2)->change(); // change precision here
            $table->decimal('price_per_unit', 12, 2)->change(); // change precision here
            $table->decimal('acquisition_price', 12, 2)->change(); // change precision here
        });
    }

    public function down()
    {
        Schema::table('csrw_item_prices', function (Blueprint $table) {
            $table->decimal('hospital_price', 8, 2)->change(); // revert back if needed
            $table->decimal('price_per_unit', 8, 2)->change(); // revert back if needed
            $table->decimal('acquisition_price', 8, 2)->change(); // revert back if needed
        });
    }
}
