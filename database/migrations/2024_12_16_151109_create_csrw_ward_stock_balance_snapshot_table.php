<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCsrwWardStockBalanceSnapshotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('csrw_ward_stock_balance_snapshot', function (Blueprint $table) {
            $table->id();
            $table->string('cl2comb')->nullable();
            $table->string('item_description')->nullable();
            $table->string('unit')->nullable();
            $table->float('unit_cost', 10, 2)->nullable();
            $table->float('beginning_balance', 10, 2)->nullable();
            $table->float('from_csr', 10, 2)->nullable();
            $table->float('from_ward', 10, 2)->nullable();
            $table->float('total_beg_bal', 10, 2)->nullable();
            $table->float('surgery', 10, 2)->nullable();
            $table->float('obgyne', 10, 2)->nullable();
            $table->float('ortho', 10, 2)->nullable();
            $table->float('pedia', 10, 2)->nullable();
            $table->float('optha', 10, 2)->nullable();
            $table->float('ent', 10, 2)->nullable();
            $table->float('total_consumption', 10, 2)->nullable();
            $table->float('total_cons_estimated_cost', 10, 2)->nullable();
            $table->float('transferred_qty', 10, 2)->nullable();
            $table->float('ending_balance', 10, 2)->nullable();
            $table->string('wardcode')->nullable();
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
        Schema::dropIfExists('csrw_ward_stock_balance_snapshot');
    }
}
