<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCsrwPatientChargeLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('csrw_patient_charge_logs', function (Blueprint $table) {
            $table->id();
            $table->string('enccode')->nullable();
            $table->string('acctno')->nullable();
            $table->bigInteger('ward_stocks_id')->nullable();
            $table->string('itemcode')->nullable();
            $table->string('from')->nullable();
            $table->dateTime('manufactured_date')->nullable();
            $table->dateTime('delivery_date')->nullable();
            $table->dateTime('expiration_date')->nullable();
            $table->integer('quantity')->nullable();
            $table->float('price_per_piece', 8, 2);
            $table->float('price_total', 8, 2);
            $table->dateTime('pcchrgdte')->nullable();
            $table->string('tscode')->nullable();
            $table->string('entry_at')->nullable(); // wardcode
            $table->string('entry_by')->nullable(); // employeeid
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
        Schema::dropIfExists('csrw_patient_charge_logs');
    }
}
