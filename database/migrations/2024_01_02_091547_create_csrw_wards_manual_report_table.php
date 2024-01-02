<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCsrwWardsManualReportTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('csrw_wards_manual_report', function (Blueprint $table) {
            $table->id();
            $table->string('cl2comb')->nullable();
            $table->string('uomcode')->nullable();
            $table->decimal('esti_budg_unit_cost', $precision = 8, $scale = 2)->nullable(); // estimated budget
            $table->integer('beginning_balance')->nullable();
            $table->integer('received_from_csr')->nullable();
            $table->integer('total_stock')->nullable();
            $table->integer('consumption_surgery')->nullable();
            $table->integer('consumption_ob_gyne')->nullable();
            $table->integer('consumption_ortho')->nullable();
            $table->integer('consumption_pedia')->nullable();
            $table->integer('consumption_optha')->nullable();
            $table->integer('consumption_ent')->nullable();
            $table->integer('total_consumption_quantity')->nullable();
            $table->decimal('total_consumption_cost', $precision = 8, $scale = 2)->nullable();
            $table->integer('ending_balance')->nullable();
            $table->integer('actual_inventory')->nullable();
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
        Schema::dropIfExists('csrw_wards_manual_report');
    }
}
