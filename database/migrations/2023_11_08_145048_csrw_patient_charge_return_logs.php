<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CsrwPatientChargeReturnLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('csrw_patient_charge_return_logs', function (Blueprint $table) {
            $table->id();
            $table->string('enccode');
            $table->string('location');
            $table->string('hpercode');
            $table->string('cl2comb');
            $table->integer('returned_qty');
            $table->string('entry_by');
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
        Schema::dropIfExists('csrw_patient_charge_return_logs');
    }
}
