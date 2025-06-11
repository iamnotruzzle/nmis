<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLoginTokenToCsrwLoginHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('csrw_login_history', function (Blueprint $table) {
            $table->uuid('login_token')->nullable()->after('wardcode');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('csrw_login_history', function (Blueprint $table) {
            $table->dropColumn('login_token');
        });
    }
}
