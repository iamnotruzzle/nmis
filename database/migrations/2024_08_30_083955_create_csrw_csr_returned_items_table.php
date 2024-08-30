<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCsrwCsrReturnedItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('csrw_csr_returned_items', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('item_conversion_id');
            $table->bigInteger('csr_stock_id');
            $table->string('ris_no');
            $table->string('cl2comb');
            $table->integer('quantity');
            $table->string('from');
            $table->string('returned_by');
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
        Schema::dropIfExists('csrw_csr_returned_items');
    }
}
