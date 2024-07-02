<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeColumnNullableInCsrwCsrItemConversionLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('csrw_csr_item_conversion_logs', function (Blueprint $table) {
            $table->string('item_conversion_id')->nullable()->change();
            $table->string('csr_stock_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('csrw_csr_item_conversion_logs', function (Blueprint $table) {
            $table->string('item_conversion_id')->nullable(false)->change();
            $table->string('csr_stock_id')->nullable(false)->change();
        });
    }
}
