<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVendingMachinesGalleriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vending_machines_galleries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vending_machine_id')->nullable()->constrained('vending_machines')->onUpdate( 'restrict')->onDelete('cascade');
            $table->string('image')->nullable();
            $table->tinyInteger('sequence')->default(10);
            $table->tinyInteger('status')->default(10);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vending_machines_galleries');
    }
}
