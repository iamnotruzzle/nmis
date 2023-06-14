<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCsrwStocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('csrw_csr_stocks', function (Blueprint $table) {
            $table->id();
            $table->string('batch_no');
            $table->string('cl2comb');
            $table->bigInteger('brand')->nullable();
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
        Schema::dropIfExists('csrw_stocks');
    }
}
