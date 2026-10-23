<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPriorityToFestiveAnimationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('festive_animations', function (Blueprint $table) {
            $table->integer( 'priority' )->default(0)->after( 'file_type' );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('festive_animations', function (Blueprint $table) {
            $table->dropColumn( 'priority' );
        });
    }
}
