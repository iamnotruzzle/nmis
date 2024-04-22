<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCsrwWardsMedsStocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('csrw_wards_meds_stocks', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('meds_request_id')->nullable();
            $table->bigInteger('reference_id')->nullable();
            $table->dateTime('dmdprdte')->nullable();
            $table->string('dmdcomb')->nullable();
            $table->string('dmdctr')->nullable();
            $table->string('fsid')->nullable(); // fund source
            $table->decimal('selling_price', $precision = 8, $scale = 2)->nullable();
            $table->integer('quantity')->nullable();
            $table->dateTime('expiration_date')->nullable();
            $table->string('wardcode')->nullable();
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
        Schema::dropIfExists('csrw_wards_meds_stocks');
    }
}
