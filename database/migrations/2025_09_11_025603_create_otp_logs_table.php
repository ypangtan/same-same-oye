<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOtpLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('otp_logs', function (Blueprint $table) {
            $table->id();
            $table->text( 'url' )->nullable();
            $table->string( 'method' )->nullable();
            $table->text( 'raw_response' )->nullable();
            $table->string( 'phone_number' )->nullable();
            $table->string( 'otp_code' )->nullable();
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
        Schema::dropIfExists('otp_logs');
    }
}
