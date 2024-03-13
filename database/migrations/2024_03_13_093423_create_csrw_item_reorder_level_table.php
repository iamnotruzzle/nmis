<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCsrwItemReorderLevelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('csrw_item_reorder_level', function (Blueprint $table) {
            $table->id();
            $table->string('cl2comb');
            $table->integer('normal_stock');
            $table->integer('alert_stock');
            $table->integer('critical_stock');
            $table->string('entry_by');
            $table->string('location');
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
        Schema::dropIfExists('csrw_item_reorder_level');
    }
}
