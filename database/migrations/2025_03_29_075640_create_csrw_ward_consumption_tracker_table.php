<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCsrwWardConsumptionTrackerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('csrw_ward_consumption_tracker', function (Blueprint $table) {
            $table->id();
            $table->bigInteger("wards_stocks_id");
            $table->bigInteger("item_conversion_id")->nullable();
            $table->string("ris_no");
            $table->string("cl2comb");
            $table->string("uomcode")->nullable();
            $table->integer("charged_qty")->nullable();
            $table->integer("surgery")->nullable();
            $table->integer("obgyne")->nullable();
            $table->integer("ortho")->nullable();
            $table->integer("pedia")->nullable();
            $table->integer("optha")->nullable();
            $table->integer("ent")->nullable();
            $table->integer("return_to_csr_qty")->nullable();
            $table->integer("transfer_qty")->nullable();
            $table->dateTime("beg_bal_date")->nullable();
            $table->dateTime("end_bal_date")->nullable();
            $table->string("item_from");
            $table->string("location");
            $table->bigInteger("price_id");
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
        Schema::dropIfExists('csrw_ward_consumption_tracker');
    }
}
