<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddZhTitleToVouchersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->string( 'en_title' )->nullable()->after( 'title' );
            $table->string( 'zh_title' )->nullable()->after( 'title' );
            $table->string( 'en_description' )->nullable()->after( 'description' );
            $table->string( 'zh_description' )->nullable()->after( 'description' );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropColumn( 'en_title' );
            $table->dropColumn( 'zh_title' );
            $table->dropColumn( 'en_description' );
            $table->dropColumn( 'zh_description' );
        });
    }
}
