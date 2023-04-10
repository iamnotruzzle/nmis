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
        Schema::create('csrw_stocks', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('batch_no');
            $table->string('cl2comb');
            $table->integer('quantity');
            $table->date('manufactured_date')->nullable();
            $table->date('delivered_date')->nullable();
            $table->date('expiration_date');
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
