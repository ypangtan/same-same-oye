<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropColumnsFromFestiveAnimationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('festive_animations', function (Blueprint $table) {
            $table->dropColumn( [ 'url', 'start_date', 'end_date', 'sequence' ] );
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
            $table->string( 'url' )->nullable()->after( 'file_type' );
            $table->date( 'start_date' )->nullable()->after( 'url' );
            $table->date( 'end_date' )->nullable()->after( 'start_date' );
            $table->integer( 'sequence' )->default(0)->after( 'end_date' );
        });
    }
}
