<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQuickDescriptionToVendingMachinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vending_machines', function (Blueprint $table) {
            $table->text('quick_description')->nullable();
        });
        Schema::table('outlets', function (Blueprint $table) {
            $table->text('quick_description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vending_machines', function (Blueprint $table) {
            $table->dropColumn('quick_description');
        });
        Schema::table('outlets', function (Blueprint $table) {
            $table->dropColumn('quick_description');
        });
    }
}
