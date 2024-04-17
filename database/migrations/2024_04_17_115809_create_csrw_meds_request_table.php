<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCsrwMedsRequestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('csrw_meds_request', function (Blueprint $table) {
            $table->id();
            $table->dateTime('dmdprdte');
            $table->string('dmdcomb');
            $table->string('dmdctr');
            $table->decimal('selling_price', $precision = 8, $scale = 2)->nullable();
            $table->integer('requested_qty');
            $table->integer('approved_qty')->nullable();
            $table->dateTime('expiration_date');
            $table->string('wardcode');
            $table->text('remarks');
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
        Schema::dropIfExists('csrw_meds_request');
    }
}
