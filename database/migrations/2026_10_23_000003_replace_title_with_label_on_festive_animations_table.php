<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReplaceTitleWithLabelOnFestiveAnimationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('festive_animations', function (Blueprint $table) {
            $table->string( 'label' )->nullable()->after( 'id' );
        });

        // Keep existing names: use en_title, fall back to zh_title
        DB::table( 'festive_animations' )->update( [
            'label' => DB::raw( 'COALESCE( en_title, zh_title )' ),
        ] );

        Schema::table('festive_animations', function (Blueprint $table) {
            $table->dropColumn( [ 'en_title', 'zh_title' ] );
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
            $table->string( 'en_title' )->nullable()->after( 'id' );
            $table->string( 'zh_title' )->nullable()->after( 'en_title' );
        });

        DB::table( 'festive_animations' )->update( [
            'en_title' => DB::raw( 'label' ),
        ] );

        Schema::table('festive_animations', function (Blueprint $table) {
            $table->dropColumn( 'label' );
        });
    }
}
