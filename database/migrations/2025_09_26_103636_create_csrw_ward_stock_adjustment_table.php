<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCsrwWardStockAdjustmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('csrw_ward_stock_adjustment', function (Blueprint $table) {
            $table->id();
            $table->string('ward_stock_id');
            $table->string('cl2comb');
            $table->integer('quantity_used');
            $table->integer('previous_quantity');
            $table->integer('new_quantity');
            $table->string('employeeid');
            $table->string('tag');
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
        Schema::dropIfExists('csrw_ward_stock_adjustment');
    }
}
