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
            $table->integer("charged_qty")->default(0);
            $table->integer("surgery")->default(0);
            $table->integer("obgyne")->default(0);
            $table->integer("ortho")->default(0);
            $table->integer("pedia")->default(0);
            $table->integer("optha")->default(0);
            $table->integer("ent")->default(0);
            $table->integer("beg_bal_qty")->default(0);
            $table->integer("end_bal_qty")->default(0);
            $table->integer("return_to_csr_qty")->default(0);
            $table->integer("transfer_qty")->default(0);
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
