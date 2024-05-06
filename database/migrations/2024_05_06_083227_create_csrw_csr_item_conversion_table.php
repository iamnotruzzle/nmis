<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCsrwCsrItemConversionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('csrw_csr_item_conversion', function (Blueprint $table) {
            $table->id();
            $table->string('csr_stock_id');
            $table->string('ris_no');
            $table->string('chrgcode')->nullable();
            $table->string('cl2comb_before');
            $table->integer('quantity_before');
            $table->string('cl2comb_after');
            $table->integer('quantity_after');
            $table->bigInteger('brand')->nullable();
            $table->string('suppcode')->nullable();
            $table->dateTime('manufactured_date')->nullable();
            $table->dateTime('delivered_date')->nullable();
            $table->dateTime('expiration_date');
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
        Schema::dropIfExists('csrw_csr_item_conversion');
    }
}
