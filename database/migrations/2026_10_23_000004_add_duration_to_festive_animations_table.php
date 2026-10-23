<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDurationToFestiveAnimationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('festive_animations', function (Blueprint $table) {
            $table->decimal( 'duration', 10, 2 )->nullable()->after( 'file_type' )->comment( 'seconds' );
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
            $table->dropColumn( 'duration' );
        });
    }
}
