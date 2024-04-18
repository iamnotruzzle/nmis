<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCsrwMedsRequest extends Migration
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
            $table->dateTime('dmdprdte')->nullable();
            $table->string('dmdcomb')->nullable();
            $table->string('dmdctr')->nullable();
            $table->decimal('selling_price', $precision = 8, $scale = 2)->nullable();
            $table->integer('requested_qty');
            $table->integer('approved_qty')->nullable();
            $table->dateTime('expiration_date')->nullable();
            $table->string('wardcode');
            $table->string('status');
            $table->text('remarks')->nullable();
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
