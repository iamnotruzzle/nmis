<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCsrwWardTransferStockTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('csrw_ward_transfer_stock', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('ward_stock_id');
            $table->string('from');
            $table->string('to');
            $table->string('requested_by');
            $table->string('approved_by');
            $table->integer('quantity');
            $table->string('remarks', 500);
            $table->string('status'); // transferred or received
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
        Schema::dropIfExists('csrw_ward_transfer_stock');
    }
}
