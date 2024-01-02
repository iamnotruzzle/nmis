<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCsrwCsrManualReportTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('csrw_csr_manual_report', function (Blueprint $table) {
            $table->id();
            $table->string('cl2comb')->nullable();
            $table->string('uomcode')->nullable();
            $table->decimal('unit_cost', $precision = 8, $scale = 2)->nullable();
            $table->integer('csr_beg_bal_quantity')->nullable();
            $table->decimal('csr_beg_bal_total_cost', $precision = 8, $scale = 2)->nullable();
            $table->integer('wards_beg_bal_quantity')->nullable();
            $table->decimal('wards_beg_bal_total_cost', $precision = 8, $scale = 2)->nullable();
            $table->integer('total_beg_bal_total_quantity')->nullable();
            $table->decimal('total_beg_bal_total_cost', $precision = 8, $scale = 2)->nullable();
            $table->integer('supp_issued_to_wards_total_quantity')->nullable();
            $table->decimal('supp_issued_to_wards_total_cost', $precision = 8, $scale = 2)->nullable();
            $table->integer('consumption_quantity')->nullable();
            $table->decimal('consumption_total_cost', $precision = 8, $scale = 2)->nullable();
            $table->integer('csr_end_bal_quantity')->nullable();
            $table->decimal('csr_end_bal_total_cost', $precision = 8, $scale = 2)->nullable();
            $table->integer('wards_end_bal_quantity')->nullable();
            $table->decimal('wards_end_bal_total_cost', $precision = 8, $scale = 2)->nullable();
            $table->integer('total_end_bal_total_quantity')->nullable();
            $table->decimal('total_end_bal_total_cost', $precision = 8, $scale = 2)->nullable();
            $table->string('entry_by')->nullable();
            $table->string('updated_by')->nullable();
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
        Schema::dropIfExists('csrw_csr_manual_report');
    }
}
