<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCsrwMedSupplyUnitConvertedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('csrw_med_supply_unit_converted', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('ward_med_supp_id');
            $table->string('wardcode');
            $table->string('orig_cl2comb');
            $table->string('orig_uomcode');
            $table->integer('orig_quantity');
            $table->string('converted_cl2comb');
            $table->string('converted_uomcode');
            $table->integer('converted_quantity');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('csrw_med_supply_unit_converted');
    }
}
