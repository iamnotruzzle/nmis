<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCsrwCsrStocksMedSuppTable extends Migration
{
    public function up()
    {
        Schema::create('csrw_csr_stocks_med_supp', function (Blueprint $table) {
            $table->id();
            $table->string('ris_no')->nullable();
            $table->string('cl2comb');
            $table->string('uomcode')->nullable();
            $table->bigInteger('supplierID')->nullable();
            $table->decimal('acquisition_price', $precision = 8, $scale = 2)->nullable();
            $table->string('chrgcode')->nullable();
            $table->integer('quantity');
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
        Schema::dropIfExists('csrw_csr_stocks_med_supp');
    }
}
