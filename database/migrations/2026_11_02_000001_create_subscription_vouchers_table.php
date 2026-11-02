<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionVouchersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscription_vouchers', function (Blueprint $table) {
            $table->id();
            $table->string( 'name' );
            $table->text( 'description' )->nullable();
            $table->unsignedSmallInteger( 'duration_in_months' )->default(1);
            $table->unsignedInteger( 'quantity' )->default(0)->comment( 'Max codes can be issued' );
            $table->timestamp( 'start_date' )->nullable()->comment( 'Claim available from' );
            $table->timestamp( 'end_date' )->nullable()->comment( 'Claim available until' );
            $table->tinyInteger( 'status' )->default(10);
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
        Schema::dropIfExists('subscription_vouchers');
    }
}
