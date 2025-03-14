<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCsrwCsrItemConversionLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('csrw_csr_item_conversion_logs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('item_conversion_id');
            $table->bigInteger('csr_stock_id');
            $table->string('ris_no');
            $table->string('chrgcode')->nullable();
            $table->string('cl2comb_before')->nullable();
            $table->integer('quantity_before');
            $table->string('cl2comb_after')->nullable();
            $table->integer('prev_qty')->nullable();
            $table->integer('new_qty')->nullable();
            $table->bigInteger('supplierID')->nullable();
            $table->dateTime('manufactured_date')->nullable();
            $table->dateTime('delivered_date')->nullable();
            $table->dateTime('expiration_date');
            $table->string('action');
            $table->string('remarks')->nullable();
            $table->string('converted_by');
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
        Schema::dropIfExists('csrw_csr_item_conversion_logs');
    }
}
