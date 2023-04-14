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
            $table->string('location');
            $table->string('status');
            $table->string('requested_by');
            $table->string('approved_by')->nullable();
            $table->dateTime('received_date')->nullable();
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
