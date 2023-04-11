<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCsrwRequestStocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('csrw_request_stocks', function (Blueprint $table) {
            $table->id();
            $table->string('cl2comb');
            $table->integer('requested_qty');
            $table->integer('approved_qty');
            $table->string('status');
            $table->string('requested_by');
            $table->string('requested_at');
            $table->string('approved_by');
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
        Schema::dropIfExists('csrw_request_stocks');
    }
}
