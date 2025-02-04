<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCupsLeftMetasToUserBundlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_bundles', function (Blueprint $table) {
            //
            $table->json('cups_left_metas')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_bundles', function (Blueprint $table) {
            //
            $table->dropColumn('cups_left_metas');
        });
    }
}
