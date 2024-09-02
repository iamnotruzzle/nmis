<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnNameToCsrwCsrReturnedItemse extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('csrw_csr_returned_items', function (Blueprint $table) {
            $table->integer('returned_to_stock')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('csrw_csr_returned_items', function (Blueprint $table) {
            $table->dropColumn('returned_to_stock');
        });
    }
}
