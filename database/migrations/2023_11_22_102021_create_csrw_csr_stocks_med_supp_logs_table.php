<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCsrwCsrStocksMedSuppLogsTable extends Migration
{
    public function up()
    {
        Schema::create('csrw_csr_stocks_med_supp_logs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('stock_id');
            $table->string('ris_no')->nullable();
            $table->string('cl2comb');
            $table->string('uomcode')->nullable();
            $table->bigInteger('supplierID')->nullable();
            $table->decimal('acquisition_price', $precision = 8, $scale = 2)->nullable();
            $table->string('chrgcode')->nullable();
            $table->integer('prev_qty');
            $table->integer('new_qty');
            $table->dateTime('manufactured_date')->nullable();
            $table->dateTime('delivered_date')->nullable();
            $table->dateTime('expiration_date');
            $table->string('action');
            $table->string('remarks', 500)->nullable();
            $table->string('entry_by'); // employeeid
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
        Schema::dropIfExists('csrw_csr_stocks_med_supp_logs');
    }
}
