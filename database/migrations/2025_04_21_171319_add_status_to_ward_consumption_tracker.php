<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusToWardConsumptionTracker extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('csrw_ward_consumption_tracker', function (Blueprint $table) {
            $table->string('status')->nullable()->after('end_bal_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('csrw_ward_consumption_tracker', function (Blueprint $table) {
            //
        });
    }
}
