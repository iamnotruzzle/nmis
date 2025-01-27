<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChangeUserIdTypeInSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Drop the existing index on user_id
        DB::statement('DROP INDEX sessions_user_id_index ON sessions');

        Schema::table('sessions', function (Blueprint $table) {
            // Change the user_id column to string (or varchar)
            $table->string('user_id', 255)->change();
        });

        // Optionally, recreate the index if needed
        Schema::table('sessions', function (Blueprint $table) {
            $table->index('user_id', 'sessions_user_id_index');
        });
    }

    public function down()
    {
        // Drop the recreated index (if you added it back)
        DB::statement('DROP INDEX sessions_user_id_index ON sessions');

        Schema::table('sessions', function (Blueprint $table) {
            // Revert the user_id column back to bigint
            $table->bigInteger('user_id')->change();
        });

        // Optionally, recreate the index on the original column type (bigint)
        Schema::table('sessions', function (Blueprint $table) {
            $table->index('user_id', 'sessions_user_id_index');
        });
    }
}
